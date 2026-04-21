<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Articulo;
use App\Models\ArticuloVariante;
use App\Models\Categoria;
use App\Models\Local;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ArticuloController extends Controller
{
    private function getLocalId(): ?int
    {
        $user = Auth::user();
        if (!$user) return null;

        $local = Local::where('id_user', $user->id)->first();
        return $local ? $local->id : null;
    }

    public function index(Request $request)
    {
        $localId = $this->getLocalId();

        if (!$localId) {
            return response()->json(['message' => 'No tenés un local asignado.'], 403);
        }

        $query = Articulo::with(['categoria', 'variantesActivas.atributoValores.atributo'])
            ->where('id_local', $localId);

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('codigo', 'like', "%{$search}%");
            });
        }

        if ($request->has('categoria') && $request->categoria !== 'Todos') {
            $query->whereHas('categoria', function ($q) use ($request) {
                $q->where('nombre', $request->categoria);
            });
        }

        if ($request->has('estado') && $request->estado !== 'todos') {
            $query->where('estado', $request->estado);
        }

        if ($request->boolean('stock_bajo')) {
            $query->where('tiene_variantes', false)->where('stock', '<=', 5)->where('stock', '>', 0);
        }

        $articulos = $query->orderBy('nombre')->get()->map(function ($art) {
            return $this->formatArticulo($art);
        });

        $categorias = Categoria::where('id_local', $localId)
            ->where('estado', 'activo')
            ->pluck('nombre')
            ->prepend('Todos');

        return response()->json([
            'products' => $articulos,
            'categories' => $categorias,
        ]);
    }

    public function show($id)
    {
        $localId = $this->getLocalId();
        $art = Articulo::with(['categoria', 'variantesActivas.atributoValores.atributo'])
            ->where('id_local', $localId)
            ->findOrFail($id);

        return response()->json(['product' => $this->formatArticulo($art)]);
    }

    public function store(Request $request)
    {
        $localId = $this->getLocalId();
        if (!$localId) return response()->json(['message' => 'Sin local activo'], 403);

        $request->validate([
            'nombre'          => 'required|string',
            'categoria'       => 'nullable|string',
            'tipo_venta'      => 'required|in:unidad,peso,volumen',
            'precio_unitario' => 'required|numeric|min:0',
            'tiene_variantes' => 'required|boolean',
        ]);

        DB::beginTransaction();
        try {
            // Find or create category
            $idCategoria = null;
            if ($request->categoria) {
                $cat = Categoria::firstOrCreate(
                    ['nombre' => $request->categoria, 'id_local' => $localId],
                    ['estado' => 'activo']
                );
                $idCategoria = $cat->id_categoria;
            }

            $articulo = Articulo::create([
                'id_local'        => $localId,
                'nombre'          => $request->nombre,
                'idcategoria'     => $idCategoria,
                'estado'          => 'activo',
                'codigo'          => $request->codigo ?? ('ART-' . strtoupper(Str::random(6))),
                'tipo_venta'      => $request->tipo_venta,
                'unidad_medida'   => $request->unidad_medida ?? 'u',
                'tiene_variantes' => $request->tiene_variantes,
                'precio_unitario' => $request->tiene_variantes ? 0 : $request->precio_unitario,
                'precio_por_unidad_medida' => $request->tiene_variantes ? 0 : $request->precio_unitario,
                'precios_por_cantidad' => $request->tiene_variantes ? null : $request->precios_por_cantidad,
                'stock'           => ($request->tipo_venta === 'unidad') ? ($request->stock ?? 0) : 0,
                'stock_decimal'   => ($request->tipo_venta !== 'unidad') ? ($request->stock ?? 0) : 0,
            ]);

            if ($request->tiene_variantes) {
                // Variations logic
                foreach ($request->variantes ?? [] as $varData) {
                    $variante = ArticuloVariante::create([
                        'idarticulo'      => $articulo->idarticulo,
                        'sku'             => $varData['sku'] ?? ArticuloVariante::generarSku($articulo, $varData['valores_nombres'] ?? []),
                        'precio_unitario' => $varData['precio'],
                        'stock'           => ($articulo->tipo_venta === 'unidad') ? ($varData['stock'] ?? 0) : 0,
                        'stock_decimal'   => ($articulo->tipo_venta !== 'unidad') ? ($varData['stock'] ?? 0) : 0,
                        'tipo_venta'      => $articulo->tipo_venta,
                        'unidad_medida'   => $articulo->unidad_medida,
                        'estado'          => 'activo',
                        'precios_por_cantidad' => $varData['precios_por_cantidad'] ?? null,
                        'es_variante_principal' => false,
                        'descripcion_variante'  => implode(', ', $varData['valores_nombres'] ?? []),
                    ]);

                    if (!empty($varData['valores_ids'])) {
                        $variante->atributoValores()->attach($varData['valores_ids']);
                    }
                }
            } else {
                // For simple products, create one "primary" variant for consistency in the list
                ArticuloVariante::create([
                    'idarticulo'      => $articulo->idarticulo,
                    'sku'             => $articulo->codigo,
                    'precio_unitario' => $articulo->precio_unitario,
                    'stock'           => $articulo->stock,
                    'stock_decimal'   => $articulo->stock_decimal,
                    'tipo_venta'      => $articulo->tipo_venta,
                    'unidad_medida'   => $articulo->unidad_medida,
                    'precios_por_cantidad' => $articulo->precios_por_cantidad,
                    'estado'          => 'activo',
                    'es_variante_principal' => true,
                    'descripcion_variante'  => 'Única',
                ]);
            }

            DB::commit();
            return response()->json([
                'message' => 'Producto creado',
                'product' => $this->formatArticulo($articulo->load(['categoria', 'variantesActivas.atributoValores.atributo'])),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al crear producto', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $localId = $this->getLocalId();
        if (!$localId) return response()->json(['message' => 'Sin local activo'], 403);

        $articulo = Articulo::where('id_local', $localId)->findOrFail($id);

        $request->validate([
            'nombre'          => 'required|string',
            'categoria'       => 'nullable|string',
            'tipo_venta'      => 'required|in:unidad,peso,volumen',
            'precio_unitario' => 'required|numeric|min:0',
            'tiene_variantes' => 'required|boolean',
        ]);

        DB::beginTransaction();
        try {
            // Find or create category
            $idCategoria = null;
            if ($request->categoria) {
                $cat = Categoria::firstOrCreate(
                    ['nombre' => $request->categoria, 'id_local' => $localId],
                    ['estado' => 'activo']
                );
                $idCategoria = $cat->id_categoria;
            }

            $articulo->update([
                'nombre'          => $request->nombre,
                'idcategoria'     => $idCategoria,
                'codigo'          => $request->codigo ?? $articulo->codigo,
                'tipo_venta'      => $request->tipo_venta,
                'unidad_medida'   => $request->unidad_medida ?? $articulo->unidad_medida ?? 'u',
                'tiene_variantes' => $request->tiene_variantes,
                'precio_unitario' => $request->tiene_variantes ? 0 : $request->precio_unitario,
                'precio_por_unidad_medida' => $request->tiene_variantes ? 0 : $request->precio_unitario,
                'precios_por_cantidad' => $request->tiene_variantes ? null : $request->precios_por_cantidad,
                'stock'           => (!$request->tiene_variantes && $request->tipo_venta === 'unidad') ? ($request->stock ?? 0) : 0,
                'stock_decimal'   => (!$request->tiene_variantes && $request->tipo_venta !== 'unidad') ? ($request->stock ?? 0) : 0,
            ]);

            // Clear old variants logic (rename before delete to avoid SKU unique constraints)
            ArticuloVariante::where('idarticulo', $articulo->idarticulo)
                ->update(['sku' => DB::raw("CONCAT(sku, '-old-', id_variante)")]);
            ArticuloVariante::where('idarticulo', $articulo->idarticulo)->delete();

            if ($request->tiene_variantes) {
                foreach ($request->variantes ?? [] as $varData) {
                    $variante = ArticuloVariante::create([
                        'idarticulo'      => $articulo->idarticulo,
                        'sku'             => $varData['sku'] ?? ArticuloVariante::generarSku($articulo, $varData['valores_nombres'] ?? []),
                        'precio_unitario' => $varData['precio'],
                        'stock'           => ($articulo->tipo_venta === 'unidad') ? ($varData['stock'] ?? 0) : 0,
                        'stock_decimal'   => ($articulo->tipo_venta !== 'unidad') ? ($varData['stock'] ?? 0) : 0,
                        'tipo_venta'      => $articulo->tipo_venta,
                        'unidad_medida'   => $articulo->unidad_medida,
                        'estado'          => 'activo',
                        'precios_por_cantidad' => $varData['precios_por_cantidad'] ?? null,
                        'es_variante_principal' => false,
                        'descripcion_variante'  => implode(', ', $varData['valores_nombres'] ?? []),
                    ]);

                    if (!empty($varData['valores_ids'])) {
                        $variante->atributoValores()->attach($varData['valores_ids']);
                    }
                }
            } else {
                // If simple product, create one "primary" variant to keep consistency in the list
                ArticuloVariante::create([
                    'idarticulo'      => $articulo->idarticulo,
                    'sku'             => $articulo->codigo,
                    'precio_unitario' => $articulo->precio_unitario,
                    'stock'           => $articulo->stock,
                    'stock_decimal'   => $articulo->stock_decimal,
                    'tipo_venta'      => $articulo->tipo_venta,
                    'unidad_medida'   => $articulo->unidad_medida,
                    'precios_por_cantidad' => $articulo->precios_por_cantidad,
                    'estado'          => 'activo',
                    'es_variante_principal' => true,
                    'descripcion_variante'  => 'Única',
                ]);
            }

            DB::commit();
            return response()->json([
                'message' => 'Producto actualizado',
                'product' => $this->formatArticulo($articulo->load(['categoria', 'variantesActivas.atributoValores.atributo'])),
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al actualizar producto', 'error' => $e->getMessage()], 500);
        }
    }

    public function updateStock(Request $request, $id)
    {
        $localId = $this->getLocalId();
        $art = Articulo::where('id_local', $localId)->findOrFail($id);

        $request->validate([
            'quantity' => 'required|numeric',
            'estado'   => 'nullable|string|in:activo,inactivo'
        ]);

        if ($request->has('estado')) {
            $art->estado = $request->estado;

            // If disabling, zero out all stock
            if ($request->estado === 'inactivo') {
                $art->stock = 0;
                $art->stock_decimal = 0;
                $art->save();
                return response()->json(['product' => $this->formatArticulo($art->load(['categoria', 'variantesActivas']))]);
            }
        }

        if ($art->tipo_venta === 'peso' || $art->tipo_venta === 'volumen') {
            $art->stock_decimal += $request->quantity;
        } else {
            $art->stock += (int)$request->quantity;
        }
        
        $art->save();

        return response()->json(['product' => $this->formatArticulo($art->load(['categoria', 'variantesActivas']))]);
    }

    public function updateVariantStock(Request $request, $id, $variantId)
    {
        $localId = $this->getLocalId();
        $articulo = Articulo::where('id_local', $localId)->findOrFail($id);
        $variante = ArticuloVariante::where('idarticulo', $articulo->idarticulo)->findOrFail($variantId);

        $request->validate([
            'quantity' => 'required|numeric',
            'estado'   => 'nullable|string|in:activo,inactivo'
        ]);

        if ($request->has('estado')) {
            $articulo->estado = $request->estado;
            $articulo->save();

            // If disabling, zero out ALL variants
            if ($request->estado === 'inactivo') {
                ArticuloVariante::where('idarticulo', $articulo->idarticulo)
                    ->update(['stock' => 0, 'stock_decimal' => 0]);
                return response()->json(['product' => $this->formatArticulo($articulo->load(['categoria', 'variantesActivas.atributoValores.atributo']))]);
            }
        }

        if ($articulo->tipo_venta === 'peso' || $articulo->tipo_venta === 'volumen') {
            $variante->stock_decimal += $request->quantity;
        } else {
            $variante->stock += (int)$request->quantity;
        }
        
        $variante->save();

        return response()->json(['product' => $this->formatArticulo($articulo->load(['categoria', 'variantesActivas.atributoValores.atributo']))]);
    }

    public function destroy($id)
    {
        $localId = $this->getLocalId();
        $articulo = Articulo::where('id_local', $localId)->findOrFail($id);
        
        DB::beginTransaction();
        try {
            // Delete associated variants first to avoid integrity issues
            $articulo->variantes()->delete();
            
            // Delete the main article
            $articulo->delete();
            
            DB::commit();
            return response()->json(['message' => 'Producto eliminado correctamente']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al eliminar producto', 'error' => $e->getMessage()], 500);
        }
    }

    private function formatArticulo(Articulo $art): array
    {
        $variantesFormateadas = [];
        $totalStock = 0;
        $minPrice = null;
        $maxPrice = null;

        if ($art->tiene_variantes && $art->relationLoaded('variantesActivas')) {
            $variantesFormateadas = $art->variantesActivas->map(function($v) use (&$totalStock, &$minPrice, &$maxPrice, $art) {
                $stock = (float) ($art->tipo_venta === 'unidad' ? $v->stock : $v->stock_decimal);
                $price = (float) $v->precio_unitario;

                $totalStock += $stock;
                if ($minPrice === null || $price < $minPrice) {
                    $minPrice = $price;
                }
                if ($maxPrice === null || $price > $maxPrice) {
                    $maxPrice = $price;
                }

                return [
                    'id'      => (string) $v->id_variante,
                    'sku'     => $v->sku,
                    'price'   => $price,
                    'stock'   => $stock,
                    'name'    => $v->descripcion_variante,
                    'precios_por_cantidad' => $v->precios_por_cantidad,
                    'valores' => $v->relationLoaded('atributoValores') ? $v->atributoValores->map(fn($av) => [
                        'id_valor' => $av->id_valor,
                        'id_atributo' => $av->id_atributo,
                        'atributo' => $av->atributo?->nombre,
                        'valor'    => $av->valor,
                    ])->toArray() : [],
                ];
            })->toArray();
        }

        $price = (float) ($art->tipo_venta === 'unidad' ? $art->precio_unitario : $art->precio_por_unidad_medida);
        $stock = (float) ($art->tipo_venta === 'unidad' ? $art->stock : $art->stock_decimal);

        // If it has variants, we use aggregated data
        if ($art->tiene_variantes && count($variantesFormateadas) > 0) {
            $price = $minPrice ?? $price;
            $stock = $totalStock;
        }

        return [
            'id'              => (string) $art->idarticulo,
            'name'            => $art->nombre,
            'price'           => $price,
            'category'        => $art->categoria?->nombre ?? 'Sin categoría',
            'stock'           => $stock,
            'precios_por_cantidad' => $art->precios_por_cantidad,
            'image'           => $art->imagen_url,
            'codigo'          => $art->codigo,
            'estado'          => $art->estado,
            'tiene_variantes' => (bool)$art->tiene_variantes,
            'max_price'       => $maxPrice ?? $price,
            'tipo_venta'      => $art->tipo_venta ?? 'unidad',
            'unidad_medida'   => $art->unidad_medida ?? 'u',
            'variantes'       => $variantesFormateadas,
        ];
    }
}
