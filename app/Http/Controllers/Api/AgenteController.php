<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Local;
use App\Models\Articulo;
use App\Models\Category;

class AgenteController extends Controller
{
    /**
     * Verificar token del agente (Sanctum)
     */
    private function checkAgente(Request $request)
    {
        if (!$request->user()) {
            abort(401, 'Token de agente inválido.');
        }
    }

    /**
     * GET /api/agente/farmacias-turno
     * Devuelve farmacias marcadas como de_turno = true
     */
    public function farmaciasTurno(Request $request)
    {
        $this->checkAgente($request);

        $farmacias = Local::where('estado', 'activo')
            ->where('tipo', 'farmacia')
            ->where('de_turno', true)
            ->select('id', 'nombre', 'direccion', 'telefono')
            ->get()
            ->map(function ($f) {
                return [
                    'nombre' => $f->nombre,
                    'direccion' => $f->direccion ?? 'Sin dirección cargada',
                    'telefono' => $f->telefono ?? 'Sin teléfono',
                ];
            });

        if ($farmacias->isEmpty()) {
            return response()->json([
                'mensaje' => 'No hay farmacias de turno registradas en este momento.',
                'farmacias' => []
            ]);
        }

        return response()->json([
            'mensaje' => 'Farmacias de turno hoy',
            'cantidad' => $farmacias->count(),
            'farmacias' => $farmacias
        ]);
    }

    /**
     * GET /api/agente/buscar-locales
     * Busca locales por texto (q), categoría o tipo
     */
    public function buscarLocales(Request $request)
    {
        $this->checkAgente($request);

        $query = Local::where('estado', 'activo')
            ->where('mostrar_feed', true);

        // Filtro por texto libre
        if ($request->has('q') && !empty($request->q)) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('descripcion', 'like', "%{$search}%");
            });
        }

        // Filtro por tipo de local
        if ($request->has('tipo') && !empty($request->tipo)) {
            $query->where('tipo', $request->tipo);
        }

        // Filtro por categoría global (slug)
        if ($request->has('categoria') && !empty($request->categoria)) {
            $slug = $request->categoria;
            $query->where(function ($q) use ($slug) {
                $q->whereHas('categories', function ($q2) use ($slug) {
                    $q2->where('slug', $slug);
                })->orWhereHas('subcategories', function ($q3) use ($slug) {
                    $q3->where('slug', $slug);
                });
            });
        }

        $locales = $query->with('categories:name,slug')
            ->select('id', 'nombre', 'direccion', 'telefono', 'tipo', 'descripcion', 'slug')
            ->orderBy('destacado', 'desc')
            ->take(10)
            ->get()
            ->map(function ($l) {
                return [
                    'nombre' => $l->nombre,
                    'direccion' => $l->direccion ?? 'Sin dirección',
                    'telefono' => $l->telefono ?? 'Sin teléfono',
                    'tipo' => $l->tipo,
                    'descripcion' => $l->descripcion ?? '',
                    'categorias' => $l->categories->pluck('name')->toArray(),
                    'link' => '/tienda/' . $l->slug,
                ];
            });

        if ($locales->isEmpty()) {
            return response()->json([
                'mensaje' => 'No se encontraron locales con esos filtros.',
                'locales' => []
            ]);
        }

        return response()->json([
            'cantidad' => $locales->count(),
            'locales' => $locales
        ]);
    }

    /**
     * GET /api/agente/buscar-productos
     * Busca productos por texto o categoría
     */
    public function buscarProductos(Request $request)
    {
        $this->checkAgente($request);

        $query = Articulo::with(['local:id,nombre,telefono,direccion,slug', 'categoria'])
            ->whereHas('local', function ($q) {
                $q->where('estado', 'activo')->where('mostrar_feed', true);
            })
            ->where('estado', 'activo')
            ->where('mostrar_feed', true);

        // Filtro por texto
        if ($request->has('q') && !empty($request->q)) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('descripcion', 'like', "%{$search}%");
            });
        }

        // Filtro por categoría del producto (nombre)
        if ($request->has('categoria') && !empty($request->categoria)) {
            $cat = $request->categoria;
            $query->whereHas('categoria', function ($q) use ($cat) {
                $q->where('nombre', 'like', "%{$cat}%");
            });
        }

        $productos = $query->select('idarticulo', 'nombre', 'precio_unitario', 'descripcion', 'id_local', 'idcategoria')
            ->orderBy('destacado', 'desc')
            ->take(10)
            ->get()
            ->map(function ($p) {
                return [
                    'producto' => $p->nombre,
                    'precio' => $p->precio_unitario,
                    'descripcion' => $p->descripcion ?? '',
                    'categoria' => $p->categoria ? $p->categoria->nombre : null,
                    'local' => $p->local ? $p->local->nombre : 'Desconocido',
                    'local_telefono' => $p->local ? ($p->local->telefono ?? 'Sin teléfono') : null,
                    'local_direccion' => $p->local ? ($p->local->direccion ?? 'Sin dirección') : null,
                    'link' => $p->local ? '/tienda/' . $p->local->slug : null,
                ];
            });

        if ($productos->isEmpty()) {
            return response()->json([
                'mensaje' => 'No se encontraron productos con esos filtros.',
                'productos' => []
            ]);
        }

        return response()->json([
            'cantidad' => $productos->count(),
            'productos' => $productos
        ]);
    }

    /**
     * GET /api/agente/anuncios
     * Devuelve anuncios activos (ofertas, alertas, info)
     */
    public function anuncios(Request $request)
    {
        $this->checkAgente($request);

        $anuncios = \App\Models\Anuncio::with('local:id,nombre')
            ->where('estado', 'activo')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($a) {
                return [
                    'titulo' => $a->titulo,
                    'descripcion' => $a->descripcion ?? '',
                    'tipo' => $a->tipo,
                    'local' => $a->local ? $a->local->nombre : 'Qhatu (Global)',
                ];
            });

        if ($anuncios->isEmpty()) {
            return response()->json([
                'mensaje' => 'No hay anuncios activos en este momento.',
                'anuncios' => []
            ]);
        }

        return response()->json([
            'cantidad' => $anuncios->count(),
            'anuncios' => $anuncios
        ]);
    }

    /**
     * GET /api/agente/categorias
     * Lista categorías globales activas del marketplace
     */
    public function categorias(Request $request)
    {
        $this->checkAgente($request);

        $categorias = Category::where('state', 'active')
            ->with('subcategories:id,category_id,name,slug')
            ->orderBy('orden')
            ->get()
            ->map(function ($c) {
                return [
                    'nombre' => $c->name,
                    'slug' => $c->slug,
                    'subcategorias' => $c->subcategories->pluck('name')->toArray(),
                ];
            });

        return response()->json([
            'cantidad' => $categorias->count(),
            'categorias' => $categorias
        ]);
    }

    /**
     * GET /api/agente/local/{slug}
     * Detalle completo de un local específico
     */
    public function showLocal(Request $request, $slug)
    {
        $this->checkAgente($request);

        $local = Local::with('categories:name,slug')
            ->where('estado', 'activo')
            ->where('mostrar_feed', true)
            ->where(function ($q) use ($slug) {
                $q->where('slug', $slug)->orWhere('id', $slug);
            })
            ->first();

        if (!$local) {
            return response()->json(['mensaje' => 'Local no encontrado.']);
        }

        return response()->json([
            'nombre' => $local->nombre,
            'direccion' => $local->direccion ?? 'Sin dirección',
            'telefono' => $local->telefono ?? 'Sin teléfono',
            'tipo' => $local->tipo,
            'descripcion' => $local->descripcion ?? '',
            'horario' => $local->horario,
            'siempre_abierto' => (bool) $local->siempre_abierto,
            'de_turno' => (bool) $local->de_turno,
            'categorias' => $local->categories->pluck('name')->toArray(),
            'link' => '/tienda/' . $local->slug,
        ]);
    }
}
