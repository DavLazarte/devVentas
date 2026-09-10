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
use Illuminate\Support\Facades\Storage;
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

        // Subquery para contar ventas (más vendidos)
        $query->withCount(['detalles as sales_count']);

        // ── Ordenamiento ────────────────────────────────────
        // - Si piden orden de ventas (POS) -> Destacados primero, luego más vendidos
        // - Sino (por defecto en Stock) -> Solo por ID descendente (últimos creados)
        if ($request->query('sort') === 'ventas' || $request->input('sort') === 'ventas') {
            $query->orderBy('destacado', 'desc');
            $query->orderBy('sales_count', 'desc');
        }
        
        $query->orderBy('idarticulo', 'desc');

        $local = Local::with('planInfo')->find($localId);
        
        $plan = $local ? $local->planInfo : null;
        if (!$plan && $local) {
            $planString = $local->plan ?? 'free';
            $maxLimit = match($planString) {
                'free' => 15,
                'basic' => 2000,
                'premium' => 999999,
                default => 15,
            };
        } else {
            $maxLimit = $plan ? $plan->max_productos : null;
        }

        $allowedIds = null;
        if ($maxLimit !== null) {
            $allowedIds = Articulo::where('id_local', $localId)
                ->orderBy('idarticulo', 'desc') // Los últimos creados son los permitidos
                ->limit($maxLimit)
                ->pluck('idarticulo')
                ->toArray();
        }

        $perPage = $request->input('per_page', $request->input('limit', 20));
        $paginator = $query->paginate($perPage);

        $articulos = collect($paginator->items())->map(function ($art) use ($allowedIds) {
            $formatted = $this->formatArticulo($art);
            if ($allowedIds !== null && !in_array($art->idarticulo, $allowedIds)) {
                $formatted['bloqueado_por_plan'] = true;
            }
            return $formatted;
        });

        $categorias = Categoria::where('id_local', $localId)
            ->where('estado', 'activo')
            ->pluck('nombre')
            ->prepend('Todos');

        return response()->json([
            'products' => $articulos,
            'categories' => $categorias,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
            ]
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

        $local = \App\Models\Local::with('planInfo')->find($localId);
        if ($local && !$local->canAddProduct()) {
            return response()->json([
                'message' => 'Has alcanzado el límite de productos de tu plan actual. Actualiza tu plan para añadir más productos.',
                'error_code' => 'PLAN_LIMIT_REACHED'
            ], 403);
        }

        $request->validate([
            'nombre'          => 'required|string',
            'categoria'       => 'nullable|string',
            'tipo_venta'      => 'required|in:unidad,peso,volumen',
            'precio_unitario' => 'required|numeric|min:0',
            'tiene_variantes' => 'required|boolean',
        ]);

        DB::beginTransaction();
        try {
            // Decode JSON strings from FormData if necessary
            $variantes = is_string($request->variantes) ? json_decode($request->variantes, true) : $request->variantes;
            $preciosPorCantidad = is_string($request->precios_por_cantidad) ? json_decode($request->precios_por_cantidad, true) : $request->precios_por_cantidad;

            // Find or create category
            $idCategoria = null;
            if ($request->categoria) {
                $cat = Categoria::firstOrCreate(
                    ['nombre' => $request->categoria, 'id_local' => $localId],
                    ['estado' => 'activo']
                );
                $idCategoria = $cat->id_categoria;
            }

            // Manejar imagen si viene en el request
            $imagenPath = null;
            if ($request->hasFile('imagen')) {
                $imagenPath = $request->file('imagen')->store('articulos', 'public');
            }

            $articulo = Articulo::create([
                'id_local'        => $localId,
                'nombre'          => $request->nombre,
                'descripcion'     => $request->descripcion,
                'idcategoria'     => $idCategoria,
                'estado'          => $request->estado ?? 'activo',
                'mostrar_feed'    => $request->boolean('mostrar_feed', true),
                'codigo'          => $request->codigo ?? ('ART-' . strtoupper(Str::random(6))),
                'tipo_venta'      => $request->tipo_venta,
                'unidad_medida'   => $request->unidad_medida ?? 'u',
                'tiene_variantes' => $request->tiene_variantes,
                'precio_unitario' => $request->tiene_variantes ? 0 : $request->precio_unitario,
                'precio_por_unidad_medida' => $request->tiene_variantes ? 0 : $request->precio_unitario,
                'precio_costo'    => $request->precio_costo ?? null,
                'precios_por_cantidad' => $request->tiene_variantes ? null : $preciosPorCantidad,
                'stock'           => ($request->tipo_venta === 'unidad') ? ($request->stock ?? 0) : 0,
                'stock_decimal'   => ($request->tipo_venta !== 'unidad') ? ($request->stock ?? 0) : 0,
                'imagen'          => $imagenPath,
            ]);

            if ($request->tiene_variantes) {
                // Variations logic
                foreach ($variantes ?? [] as $varData) {
                    $valoresIds = $varData['valores_ids'] ?? [];
                    $hash = ArticuloVariante::generarHash($valoresIds);

                    $variante = ArticuloVariante::create([
                        'idarticulo'      => $articulo->idarticulo,
                        'sku'             => $varData['sku'] ?? ArticuloVariante::generarSku($articulo, $varData['valores_nombres'] ?? []),
                        'precio_unitario' => $varData['precio'],
                        'precio_costo'    => $varData['precio_costo'] ?? null,
                        'stock'           => ($articulo->tipo_venta === 'unidad') ? ($varData['stock'] ?? 0) : 0,
                        'stock_decimal'   => ($articulo->tipo_venta !== 'unidad') ? ($varData['stock'] ?? 0) : 0,
                        'tipo_venta'      => $articulo->tipo_venta,
                        'unidad_medida'   => $articulo->unidad_medida,
                        'estado'          => 'activo',
                        'precios_por_cantidad' => $varData['precios_por_cantidad'] ?? null,
                        'es_variante_principal' => false,
                        'descripcion_variante'  => implode(', ', $varData['valores_nombres'] ?? []),
                        'combination_hash'      => $hash,
                    ]);

                    if (!empty($valoresIds)) {
                        $variante->atributoValores()->attach($valoresIds);
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
                    'combination_hash'      => 'primary',
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
            // Decode JSON strings from FormData if necessary
            $variantes = is_string($request->variantes) ? json_decode($request->variantes, true) : $request->variantes;
            $preciosPorCantidad = is_string($request->precios_por_cantidad) ? json_decode($request->precios_por_cantidad, true) : $request->precios_por_cantidad;

            // Find or create category
            $idCategoria = null;
            if ($request->categoria) {
                $cat = Categoria::firstOrCreate(
                    ['nombre' => $request->categoria, 'id_local' => $localId],
                    ['estado' => 'activo']
                );
                $idCategoria = $cat->id_categoria;
            }

            // Manejar imagen si viene en el request
            if ($request->hasFile('imagen')) {
                // Borrar imagen anterior si existe
                if ($articulo->imagen) {
                    Storage::disk('public')->delete($articulo->imagen);
                }
                $imagenPath = $request->file('imagen')->store('articulos', 'public');
                $articulo->imagen = $imagenPath;
            }

            $articulo->update([
                'nombre'          => $request->nombre,
                'descripcion'     => $request->descripcion,
                'idcategoria'     => $idCategoria,
                'codigo'          => $request->codigo ?? $articulo->codigo,
                'tipo_venta'      => $request->tipo_venta,
                'unidad_medida'   => $request->unidad_medida ?? $articulo->unidad_medida ?? 'u',
                'tiene_variantes' => $request->tiene_variantes,
                'precio_unitario' => $request->tiene_variantes ? 0 : $request->precio_unitario,
                'precio_por_unidad_medida' => $request->tiene_variantes ? 0 : $request->precio_unitario,
                'precio_costo'    => array_key_exists('precio_costo', $request->all()) ? $request->precio_costo : $articulo->precio_costo,
                'precios_por_cantidad' => $request->tiene_variantes ? null : $preciosPorCantidad,
                'stock'           => (!$request->tiene_variantes && $request->tipo_venta === 'unidad') ? ($request->stock ?? 0) : 0,
                'stock_decimal'   => (!$request->tiene_variantes && $request->tipo_venta !== 'unidad') ? ($request->stock ?? 0) : 0,
                'estado'          => $request->estado ?? $articulo->estado,
                'mostrar_feed'    => $request->has('mostrar_feed') ? $request->boolean('mostrar_feed') : $articulo->mostrar_feed,
                'imagen'          => $articulo->imagen,
            ]);

            // 1. Obtener todas las variantes existentes de este artículo (incluso las eliminadas)
            $variantesExistentes = ArticuloVariante::withTrashed()
                ->where('idarticulo', $articulo->idarticulo)
                ->get();

            if ($request->tiene_variantes) {
                $hashDeseados = [];

                foreach ($variantes ?? [] as $varData) {
                    $valoresIds = $varData['valores_ids'] ?? [];
                    $hash = ArticuloVariante::generarHash($valoresIds);
                    $hashDeseados[] = $hash;

                    // 2. Buscar si existe una variante con esta firma (hash)
                    $varianteExistente = $variantesExistentes->firstWhere('combination_hash', $hash);

                    if ($varianteExistente) {
                        // 2a. Si existe, la restauramos (si estaba eliminada) y actualizamos
                        if ($varianteExistente->trashed()) {
                            $varianteExistente->restore();
                        }
                        $varianteExistente->update([
                            'sku'             => $varData['sku'] ?? $varianteExistente->sku,
                            'precio_unitario' => $varData['precio'],
                            'precio_costo'    => array_key_exists('precio_costo', $varData) ? $varData['precio_costo'] : $varianteExistente->precio_costo,
                            'stock'           => ($articulo->tipo_venta === 'unidad') ? ($varData['stock'] ?? 0) : 0,
                            'stock_decimal'   => ($articulo->tipo_venta !== 'unidad') ? ($varData['stock'] ?? 0) : 0,
                            'precios_por_cantidad' => $varData['precios_por_cantidad'] ?? null,
                            'descripcion_variante' => implode(', ', $varData['valores_nombres'] ?? []),
                        ]);
                        
                        // Sincronizar atributos por si hubo algún cambio menor, pero la firma garantiza la identidad
                        if (!empty($valoresIds)) {
                            $varianteExistente->atributoValores()->sync($valoresIds);
                        }
                    } else {
                        // 2b. Si NO existe, se crea una variante completamente nueva
                        $variante = ArticuloVariante::create([
                            'idarticulo'      => $articulo->idarticulo,
                            'sku'             => $varData['sku'] ?? ArticuloVariante::generarSku($articulo, $varData['valores_nombres'] ?? []),
                            'precio_unitario' => $varData['precio'],
                            'precio_costo'    => $varData['precio_costo'] ?? null,
                            'stock'           => ($articulo->tipo_venta === 'unidad') ? ($varData['stock'] ?? 0) : 0,
                            'stock_decimal'   => ($articulo->tipo_venta !== 'unidad') ? ($varData['stock'] ?? 0) : 0,
                            'tipo_venta'      => $articulo->tipo_venta,
                            'unidad_medida'   => $articulo->unidad_medida,
                            'estado'          => 'activo',
                            'precios_por_cantidad' => $varData['precios_por_cantidad'] ?? null,
                            'es_variante_principal' => false,
                            'descripcion_variante'  => implode(', ', $varData['valores_nombres'] ?? []),
                            'combination_hash'      => $hash,
                        ]);

                        if (!empty($valoresIds)) {
                            $variante->atributoValores()->attach($valoresIds);
                        }
                    }
                }

                // 3. Hacer SOFT DELETE de las variantes que ya NO vinieron en el request
                foreach ($variantesExistentes as $vExistente) {
                    if (!$vExistente->es_variante_principal && !in_array($vExistente->combination_hash, $hashDeseados)) {
                        $vExistente->delete(); // Esto setea deleted_at (Soft Delete)
                    }
                    if ($vExistente->es_variante_principal) {
                        $vExistente->delete(); // Eliminar la variante simple si cambiamos a variantes
                    }
                }
            } else {
                // Producto simple: buscamos si ya existía una variante "principal"
                $variantePrincipal = $variantesExistentes->firstWhere('es_variante_principal', true);

                if ($variantePrincipal) {
                    if ($variantePrincipal->trashed()) {
                        $variantePrincipal->restore();
                    }
                    $variantePrincipal->update([
                        'sku'             => $articulo->codigo,
                        'precio_unitario' => $articulo->precio_unitario,
                        'stock'           => $articulo->stock,
                        'stock_decimal'   => $articulo->stock_decimal,
                        'precios_por_cantidad' => $articulo->precios_por_cantidad,
                    ]);
                } else {
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
                        'combination_hash'      => 'primary',
                    ]);
                }

                // Soft Delete de todas las demás (si pasamos de múltiples a simple)
                foreach ($variantesExistentes as $vExistente) {
                    if (!$vExistente->es_variante_principal) {
                        $vExistente->delete();
                    }
                }
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
                    'precio_costo' => $v->precio_costo !== null ? (float) $v->precio_costo : null,
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
            'descripcion'     => $art->descripcion,
            'price'           => $price,
            'category'        => $art->categoria?->nombre ?? 'Sin categoría',
            'stock'           => $stock,
            'precios_por_cantidad' => $art->precios_por_cantidad,
            'image'           => $art->imagen_url,
            'codigo'          => $art->codigo,
            'estado'          => $art->estado,
            'mostrar_feed'    => (bool) $art->mostrar_feed,
            'tiene_variantes' => (bool) $art->tiene_variantes,
            'max_price'       => $maxPrice ?? $price,
            'tipo_venta'      => $art->tipo_venta ?? 'unidad',
            'unidad_medida'   => $art->unidad_medida ?? 'u',
            'precio_costo'    => $art->precio_costo !== null ? (float) $art->precio_costo : null,
            'variantes'       => $variantesFormateadas,
        ];
    }
}
