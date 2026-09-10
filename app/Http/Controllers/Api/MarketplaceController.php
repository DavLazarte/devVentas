<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Local;
use App\Models\Articulo;

class MarketplaceController extends Controller
{
    /**
     * Obtener todas las categorías globales del marketplace con sus subcategorías
     */
    public function categorias()
    {
        $categorias = Category::with('subcategories')
            ->where('state', 'active')
            ->orderBy('orden')
            ->get();
            
        return response()->json($categorias);
    }

    /**
     * Obtener los anuncios activos del marketplace
     */
    public function anuncios()
    {
        $anuncios = \App\Models\Anuncio::with('local:id,nombre,foto_logo,tipo,slug')
            ->where('estado', 'activo')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
            
        // Auto-generar anuncios para Farmacias de turno
        $farmaciasTurno = Local::where('estado', 'activo')
            ->where('tipo', 'farmacia')
            ->where('de_turno', true)
            ->select('id', 'nombre', 'foto_logo', 'tipo', 'slug', 'direccion')
            ->get()
            ->map(function ($farmacia) {
                return [
                    'id' => 'auto-farmacia-' . $farmacia->id,
                    'local_id' => $farmacia->id,
                    'titulo' => 'Hoy de turno',
                    'descripcion' => $farmacia->direccion ? "Dirección: " . $farmacia->direccion : 'Farmacia de turno habilitada en el día de hoy.',
                    'tipo' => 'farmacia_turno', // Changed to identify it easily in frontend
                    'estado' => 'activo',
                    'local' => $farmacia,
                    'created_at' => now(),
                ];
            });

        // Combinar y ordenar
        $combinados = collect($anuncios)->concat($farmaciasTurno)
            ->sortByDesc('created_at')
            ->take(15)
            ->values();

        return response()->json($combinados);
    }

    /**
     * Obtener locales activos que se muestran en el feed
     */
    public function locales(Request $request)
    {
        $query = Local::where('estado', 'activo')
            ->where('mostrar_feed', true);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('descripcion', 'like', "%{$search}%");
            });
        }
        
        if ($request->has('categoria')) {
            $slug = $request->categoria;
            $query->where(function($q) use ($slug) {
                $q->whereHas('categories', function($q2) use ($slug) {
                    $q2->where('slug', $slug);
                })->orWhereHas('subcategories', function($q3) use ($slug) {
                    $q3->where('slug', $slug);
                });
            });
        }

        // Filtro por tipo (tienda=venta, servicio=servicio)
        if ($request->has('tipo')) {
            $tipo = $request->tipo;
            if ($tipo === 'tienda') {
                $query->where('tipo', 'venta');
            } elseif ($tipo === 'servicio') {
                $query->where('tipo', 'servicio');
            } else {
                $query->where('tipo', $tipo);
            }
        }

        $locales = $query->orderBy('created_at', 'desc')->get();

        return response()->json($locales);
    }

    /**
     * Obtener locales recomendados (5 aleatorios)
     */
    public function recomendados()
    {
        $locales = Local::where('estado', 'activo')
            ->where('mostrar_feed', true)
            ->inRandomOrder()
            ->take(5)
            ->get();

        return response()->json($locales);
    }

    /**
     * Obtener locales destacados
     */
    public function destacados()
    {
        $locales = Local::where('estado', 'activo')
            ->where('mostrar_feed', true)
            ->where('destacado', true)
            ->orderBy('rating_promedio', 'desc')
            ->take(10)
            ->get();

        return response()->json($locales);
    }

    /**
     * Obtener 5 locales nuevos (solo planes pago, para el feed home)
     */
    public function nuevos()
    {
        $locales = Local::where('estado', 'activo')
            ->where('mostrar_feed', true)
            ->where(function($q) {
                // Solo planes pago: plan != 'free' o tiene plan_id con plan de pago
                $q->where('plan', '!=', 'free')
                  ->orWhereHas('planInfo', function($pq) {
                      $pq->where('precio_mensual', '>', 0);
                  });
            })
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return response()->json($locales);
    }

    /**
     * Listado profundo de locales nuevos para la página /tienda/nuevos
     * Últimos 20, priorizando los que pagan
     */
    public function nuevosTodos()
    {
        // Traemos los de planes pago primero, luego el resto
        $pagos = Local::where('estado', 'activo')
            ->where('mostrar_feed', true)
            ->where(function($q) {
                $q->where('plan', '!=', 'free')
                  ->orWhereHas('planInfo', function($pq) {
                      $pq->where('precio_mensual', '>', 0);
                  });
            })
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        $gratis = Local::where('estado', 'activo')
            ->where('mostrar_feed', true)
            ->where(function($q) {
                $q->where('plan', 'free')
                  ->whereDoesntHave('planInfo', function($pq) {
                      $pq->where('precio_mensual', '>', 0);
                  });
            })
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        $todos = $pagos->merge($gratis)->take(20)->values();

        return response()->json($todos);
    }

    /**
     * Obtener locales de la misma localidad ("Cerca tuyo")
     */
    public function cercaTuyo(Request $request)
    {
        $request->validate([
            'localidad' => 'required|string|max:100',
        ]);

        $localidad = $request->localidad;

        $locales = Local::where('estado', 'activo')
            ->where('mostrar_feed', true)
            ->whereRaw('LOWER(localidad) = ?', [mb_strtolower($localidad)])
            ->orderBy('rating_promedio', 'desc')
            ->take(10)
            ->get();

        return response()->json($locales);
    }

    /**
     * Obtener perfil público de un local específico
     */
    public function showLocal($identifier)
    {
        $local = Local::with(['categories'])
            ->where('estado', 'activo')
            ->where('mostrar_feed', true)
            ->where(function($q) use ($identifier) {
                $q->where('slug', $identifier)
                  ->orWhere('id', $identifier);
            })
            ->firstOrFail();
            
        return response()->json($local);
    }

    /**
     * Obtener productos públicos de un local
     */
    public function productosLocal($identifier, Request $request)
    {
        $local = Local::with('planInfo')->where('estado', 'activo')
            ->where('mostrar_feed', true)
            ->where(function($q) use ($identifier) {
                $q->where('slug', $identifier)
                  ->orWhere('id', $identifier);
            })
            ->firstOrFail();
            
        $query = Articulo::with(['categoria', 'variantesActivas.atributoValores.atributo'])
            ->where('id_local', $local->id)
            ->where('estado', 'activo')
            ->where('mostrar_feed', true);

        // Apply plan restrictions to public feed (hide blocked products)
        $plan = $local->planInfo;
        if (!$plan) {
            $planString = $local->plan ?? 'free';
            $maxLimit = match($planString) {
                'free' => 10, // Max 10 in feed for Free plan
                'basic' => 2000,
                'premium' => 999999,
                default => 10,
            };
        } else {
            $maxLimit = $plan->slug === 'free' ? 10 : ($plan->max_productos ?? 999999);
        }

        if ($maxLimit !== null) {
            $allowedIds = Articulo::where('id_local', $local->id)
                ->orderBy('idarticulo', 'desc')
                ->limit($maxLimit)
                ->pluck('idarticulo')
                ->toArray();
            
            $query->whereIn('idarticulo', $allowedIds);
        }
            
        if ($request->has('destacados')) {
            $query->where('destacado', true);
        }

        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('nombre', 'like', $searchTerm)
                  ->orWhere('descripcion', 'like', $searchTerm);
            });
        }
        $productos = $query->orderBy('idarticulo', 'desc')->paginate(10);
            
        return response()->json($productos);
    }
    
    /**
     * Obtener servicios públicos de un local
     */
    public function serviciosLocal($identifier)
    {
        $local = Local::where('estado', 'activo')
            ->where('mostrar_feed', true)
            ->where(function($q) use ($identifier) {
                $q->where('slug', $identifier)
                  ->orWhere('id', $identifier);
            })
            ->firstOrFail();
            
        $servicios = \App\Models\Servicio::with('recursos')
            ->where('id_local', $local->id)
            ->where('estado', true)
            ->where('mostrar_feed', true)
            ->where('es_reservable', true)
            ->orderBy('nombre', 'asc')
            ->get();
            
        return response()->json($servicios);
    }
    
    /**
     * Productos destacados a nivel global
     * Prioridad: locales con plan pago primero
     */
    public function tendencias()
    {
        // Productos destacados de locales con plan pago
        $destacadosPago = Articulo::with(['local', 'categoria'])
            ->whereHas('local', function($q) {
                $q->where('estado', 'activo')
                  ->where('mostrar_feed', true)
                  ->where(function($pq) {
                      $pq->where('plan', '!=', 'free')
                         ->orWhereHas('planInfo', function($ppq) {
                             $ppq->where('precio_mensual', '>', 0);
                         });
                  });
            })
            ->where('estado', 'activo')
            ->where('mostrar_feed', true)
            ->where('destacado', true)
            ->inRandomOrder()
            ->take(10)
            ->get();

        // Completar con gratuitos si no hay suficientes
        if ($destacadosPago->count() < 10) {
            $idsUsados = $destacadosPago->pluck('idarticulo');
            $extra = Articulo::with(['local', 'categoria'])
                ->whereHas('local', function($q) {
                    $q->where('estado', 'activo')->where('mostrar_feed', true);
                })
                ->where('estado', 'activo')
                ->where('mostrar_feed', true)
                ->where('destacado', true)
                ->whereNotIn('idarticulo', $idsUsados)
                ->inRandomOrder()
                ->take(10 - $destacadosPago->count())
                ->get();
            $destacadosPago = $destacadosPago->merge($extra);
        }
            
        return response()->json($destacadosPago->values());
    }

    /**
     * Obtener detalle de un producto específico
     */
    public function showProducto($id)
    {
        $producto = Articulo::with(['local', 'categoria', 'variantesActivas.atributoValores.atributo'])
            ->where('estado', 'activo')
            ->where('mostrar_feed', true)
            ->findOrFail($id);
            
        return response()->json($producto);
    }

    /**
     * Crear un pedido desde el marketplace
     */
    public function createPedido(Request $request)
    {
        $request->validate([
            'id_local' => 'required|exists:locales,id',
            'nombre_cliente' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'items' => 'required|array|min:1',
            'items.*.idarticulo' => 'required',
            'items.*.id_variante' => 'nullable',
            'items.*.cantidad' => 'required|integer|min:1',
            'items.*.precio_unitario' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'metodo_pago' => 'required|in:efectivo,tarjeta'
        ]);

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $pedido = \App\Models\Pedido::create([
                'id_local' => $request->id_local,
                'nombre_cliente' => $request->nombre_cliente,
                'telefono' => $request->telefono,
                'email' => $request->email,
                'estado' => 'pendiente',
                'total' => $request->total,
                'subtotal' => $request->total,
                'tipo_pedido' => 'producto',
                'metodo_pago' => $request->metodo_pago,
                'id_user' => auth('sanctum')->id() ?? null,
            ]);

            foreach ($request->items as $item) {
                $subtotal = $item['cantidad'] * $item['precio_unitario'];
                $variante = null;
                if (!empty($item['id_variante'])) {
                    $variante = \App\Models\ArticuloVariante::find($item['id_variante']);
                }

                \App\Models\DetallePedido::create([
                    'pedido_id' => $pedido->id,
                    'idarticulo' => $item['idarticulo'],
                    'id_variante' => $item['id_variante'] ?? null,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'subtotal' => $subtotal,
                    'sku_vendido' => $variante ? $variante->sku : null,
                    'descripcion_variante' => $variante ? $variante->descripcion_variante : null,
                ]);
            }

            \Illuminate\Support\Facades\DB::commit();
            return response()->json(['success' => true, 'pedido_id' => $pedido->id, 'message' => 'Pedido creado exitosamente']);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Rastrear pedidos como invitado usando IDs guardados localmente
     */
    public function trackPedidos(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer'
        ]);

        $pedidos = \App\Models\Pedido::with(['local'])
            ->whereIn('id', $request->ids)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($pedido) {
                // Formatting for the frontend
                return [
                    'id' => $pedido->id,
                    'nombre_cliente' => $pedido->nombre_cliente,
                    'estado' => $pedido->estado,
                    'total' => (float) $pedido->total,
                    'fecha' => $pedido->created_at->format('Y-m-d H:i'),
                    'local_nombre' => $pedido->local ? $pedido->local->nombre : 'Local Desconocido',
                    'local_logo' => $pedido->local ? ($pedido->local->foto_logo ?? $pedido->local->logo) : null,
                ];
            });

        return response()->json(['success' => true, 'pedidos' => $pedidos]);
    }
}
