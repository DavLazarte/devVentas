<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Local;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SuperadminController extends Controller
{
    private function checkAdmin(Request $request)
    {
        $user = $request->user();
        // Adjust role check to match how auth works: role_id = 1 or role->name = 'admin'
        if (!$user || ($user->role_id != 1 && (!isset($user->role) || $user->role->name !== 'admin'))) {
            abort(403, 'No autorizado. Solo súper administradores.');
        }
    }

    public function getLocales(Request $request)
    {
        $this->checkAdmin($request);

        $query = Local::with([
            'user:id,name,email,role_id',
            'categories:id,name,slug,icon',
            'subcategories:id,name,slug,category_id',
        ]);

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('direccion', 'like', "%{$search}%")
                  ->orWhere('telefono', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($qu) use ($search) {
                      $qu->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('tipo') && !empty($request->tipo) && $request->tipo !== 'todos') {
            $query->where('tipo', $request->tipo);
        }

        if ($request->has('categoria_id') && !empty($request->categoria_id)) {
            $catId = $request->categoria_id;
            $query->whereHas('categories', function ($q) use ($catId) {
                $q->where('categories.id', $catId);
            });
        }

        $perPage = min((int) $request->input('per_page', 15), 200);
        $paginator = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // Farmacias efectivamente de turno hoy (ciclo COFATUC 08:00 a 08:00 + overrides manuales del día)
        $farmaciasTurnoHoy = Local::obtenerFarmaciasDeTurno()->whereNotNull('id')->keyBy('id');
        $fechaTurnoActual = Local::fechaTurnoActual();

        $formateado = $paginator->getCollection()->map(function ($l) use ($farmaciasTurnoHoy, $fechaTurnoActual) {
            $redes = is_string($l->redes_sociales) ? json_decode($l->redes_sociales, true) : $l->redes_sociales;
            $esFarmacia = $l->tipo === 'farmacia';
            $enTurnoHoy = $esFarmacia && $farmaciasTurnoHoy->has($l->id);
            $fechaOverride = $l->turno_fecha ? substr((string) $l->turno_fecha, 0, 10) : null;
            $bajadaHoy = $esFarmacia && !$enTurnoHoy && $fechaOverride === $fechaTurnoActual && !$l->de_turno;
            $turnoOrigen = $enTurnoHoy
                ? ($farmaciasTurnoHoy->get($l->id)['origen'] ?? 'cofatuc')
                : ($bajadaHoy ? 'bajada_hoy' : null);

            return [
                'id' => $l->id,
                'nombre' => $l->nombre,
                'slug' => $l->slug,
                'email' => $l->email,
                'telefono' => $l->telefono,
                'direccion' => $l->direccion,
                'localidad' => $l->localidad,
                'descripcion' => $l->descripcion,
                'horario' => $l->horario,
                'redes_sociales' => $redes ?: null,
                'instagram' => is_array($redes) ? ($redes['instagram'] ?? '') : '',
                'estado' => $l->estado,
                'plan' => $l->plan,
                'tipo' => $l->tipo,
                'destacado' => (bool) $l->destacado,
                'mostrar_feed' => (bool) $l->mostrar_feed,
                'siempre_abierto' => (bool) $l->siempre_abierto,
                'de_turno' => $enTurnoHoy,
                'turno_origen' => $turnoOrigen,
                'vistas_perfil' => (int) ($l->vistas_perfil ?? 0),
                'clicks_whatsapp' => (int) ($l->clicks_whatsapp ?? 0),
                'foto_logo_url' => $l->foto_logo_url,
                'foto_portada_url' => $l->foto_portada_url,
                'categories' => $l->categories->map(fn($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'slug' => $c->slug,
                    'icon' => $c->icon,
                ])->values(),
                'subcategories' => $l->subcategories->map(fn($s) => [
                    'id' => $s->id,
                    'name' => $s->name,
                    'slug' => $s->slug,
                    'category_id' => $s->category_id,
                ])->values(),
                'owner' => $l->user ? [
                    'id' => $l->user->id,
                    'name' => $l->user->name,
                    'email' => $l->user->email,
                ] : null,
                'created_at' => $l->created_at,
            ];
        });

        return response()->json([
            'locales' => $formateado,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage()
            ]
        ]);
    }

    public function storeTenant(Request $request)
    {
        $this->checkAdmin($request);

        $request->validate([
            // User validations
            'user_name' => 'required|string|max:255',
            'user_email' => 'required|email|unique:users,email',
            'user_password' => 'required|min:6',

            // Local validations
            'local_nombre' => 'required|string|max:255',
            'local_slug' => 'nullable|string|unique:locales,slug',
            'plan' => 'required|string',
            'tipo' => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            // 1. Create the Owner User
            // Usually role_id = 2 is for normal users or 'tenant' owner
            // If voyager provides a specific role for tenant admin, we use that. Fallback to 2.
            $userRole = 2; // Can be adjusted as needed
            
            $user = User::create([
                'name' => $request->user_name,
                'email' => $request->user_email,
                'password' => Hash::make($request->user_password),
                'role_id' => $userRole,
            ]);

            // 2. Create the Local and assign it to the user
            $slug = $request->local_slug ?: Str::slug($request->local_nombre);
            
            // Ensure slug is unique
            $originalSlug = $slug;
            $count = 1;
            while(Local::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }

            $local = Local::create([
                'id_user' => $user->id,
                'nombre' => $request->local_nombre,
                'slug' => $slug,
                'estado' => 'activo',
                'plan' => $request->plan ?? 'free',
                'tipo' => $request->tipo ?? 'venta',
                'mostrar_feed' => true,
                'email' => $request->user_email,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Inquilino y Local creados con éxito',
                'tenant' => [
                    'id' => $local->id,
                    'nombre' => $local->nombre,
                    'slug' => $local->slug,
                    'email' => $local->email,
                    'estado' => $local->estado,
                    'owner' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al crear',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function parseIdArray($input): array
    {
        if (is_array($input)) {
            return array_values(array_filter(array_map('intval', $input)));
        }
        if (is_string($input) && !empty($input)) {
            $decoded = json_decode($input, true);
            if (is_array($decoded)) {
                return array_values(array_filter(array_map('intval', $decoded)));
            }
            return array_values(array_filter(array_map('intval', explode(',', $input))));
        }
        return [];
    }

    public function storePublicLocal(Request $request)
    {
        $this->checkAdmin($request);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|string|max:50',
            'telefono' => 'nullable|string|max:100',
            'direccion' => 'nullable|string|max:255',
            'localidad' => 'nullable|string|max:100',
            'descripcion' => 'nullable|string',
            'horario' => 'nullable|string',
            'siempre_abierto' => 'nullable',
            'destacado' => 'nullable',
            'de_turno' => 'nullable',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'portada' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $slug = Str::slug($request->nombre);
        $originalSlug = $slug;
        $count = 1;
        while(Local::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        $planFreeId = \App\Models\Plan::where('slug', 'free')->value('id');

        $local = new Local([
            'id_user' => $request->user()->id, // Propiedad temporal del admin
            'nombre' => $request->nombre,
            'slug' => $slug,
            'estado' => 'activo',
            'plan' => 'free',
            'plan_id' => $planFreeId,
            'tipo' => $request->tipo,
            'mostrar_feed' => true,
            'telefono' => $request->telefono,
            'direccion' => $request->direccion,
            'localidad' => $request->input('localidad', 'Monteros'),
            'provincia' => $request->input('provincia', 'Tucumán'),
            'descripcion' => $request->descripcion,
            'horario' => $request->horario,
            'redes_sociales' => $request->has('instagram')
                ? json_encode(['instagram' => trim((string) $request->input('instagram'))])
                : ($request->has('redes_sociales') ? (is_array($request->redes_sociales) ? json_encode($request->redes_sociales) : $request->redes_sociales) : null),
            'siempre_abierto' => filter_var($request->input('siempre_abierto', false), FILTER_VALIDATE_BOOLEAN),
            'destacado' => filter_var($request->input('destacado', false), FILTER_VALIDATE_BOOLEAN),
            'de_turno' => filter_var($request->input('de_turno', false), FILTER_VALIDATE_BOOLEAN),
            'turno_fecha' => filter_var($request->input('de_turno', false), FILTER_VALIDATE_BOOLEAN)
                ? Local::fechaTurnoActual()
                : null,
        ]);

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('locales/logos', $filename, 'public');
            $local->foto_logo = 'locales/logos/' . $filename;
        }

        if ($request->hasFile('portada')) {
            $file = $request->file('portada');
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('locales/portadas', $filename, 'public');
            $local->foto_portada = 'locales/portadas/' . $filename;
        }

        $local->save();

        if ($request->has('categories')) {
            $local->categories()->sync($this->parseIdArray($request->input('categories')));
        }
        if ($request->has('subcategories')) {
            $local->subcategories()->sync($this->parseIdArray($request->input('subcategories')));
        }

        \Illuminate\Support\Facades\Cache::forget('marketplace_feed_home');
        Local::limpiarCacheFarmaciasTurno();

        return response()->json([
            'message' => 'Local público creado con éxito',
            'local' => $local->load(['categories', 'subcategories'])
        ], 201);
    }

    public function transferLocal(Request $request, $id)
    {
        $this->checkAdmin($request);

        $request->validate([
            'new_owner_email' => 'required|email'
        ]);

        $local = Local::findOrFail($id);
        $newUser = User::where('email', $request->new_owner_email)->first();

        if (!$newUser) {
            return response()->json(['message' => 'Usuario no encontrado con ese email'], 404);
        }

        $local->id_user = $newUser->id;
        $local->save();

        return response()->json([
            'message' => 'Propiedad transferida con éxito',
            'local' => $local
        ]);
    }

    public function storeAnuncio(Request $request, $id)
    {
        $this->checkAdmin($request);

        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'tipo' => 'required|string',
            'en_slider' => 'nullable',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $local = Local::findOrFail($id);

        $imagenPath = null;
        if ($request->hasFile('imagen')) {
            $file = $request->file('imagen');
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('anuncios', $filename, 'public');
            $imagenPath = 'anuncios/' . $filename;
        }

        $anuncio = \App\Models\Anuncio::create([
            'local_id' => $local->id,
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'imagen' => $imagenPath,
            'tipo' => $request->tipo,
            'estado' => 'activo',
            'en_slider' => filter_var($request->input('en_slider', false), FILTER_VALIDATE_BOOLEAN),
        ]);

        \Illuminate\Support\Facades\Cache::forget('marketplace_feed_home');

        return response()->json([
            'message' => 'Anuncio creado correctamente',
            'anuncio' => $anuncio->load('local:id,nombre,slug,foto_logo')
        ], 201);
    }

    public function storeGlobalAnuncio(Request $request)
    {
        $this->checkAdmin($request);

        $request->validate([
            'local_id' => 'nullable|exists:locales,id',
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'tipo' => 'required|string',
            'estado' => 'nullable|string|in:activo,inactivo',
            'en_slider' => 'nullable',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $imagenPath = null;
        if ($request->hasFile('imagen')) {
            $file = $request->file('imagen');
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('anuncios', $filename, 'public');
            $imagenPath = 'anuncios/' . $filename;
        }

        $anuncio = \App\Models\Anuncio::create([
            'local_id' => $request->input('local_id') ?: null,
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'imagen' => $imagenPath,
            'tipo' => $request->tipo,
            'estado' => $request->input('estado', 'activo'),
            'en_slider' => filter_var($request->input('en_slider', false), FILTER_VALIDATE_BOOLEAN),
        ]);

        \Illuminate\Support\Facades\Cache::forget('marketplace_feed_home');

        return response()->json([
            'message' => 'Anuncio creado correctamente',
            'anuncio' => $anuncio->load('local:id,nombre,slug,foto_logo')
        ], 201);
    }

    public function getAnuncios(Request $request)
    {
        $this->checkAdmin($request);
        
        $query = \App\Models\Anuncio::with('local:id,nombre,slug,foto_logo');

        if ($request->has('search') && !empty($request->search)) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('titulo', 'like', "%{$s}%")
                  ->orWhere('descripcion', 'like', "%{$s}%")
                  ->orWhereHas('local', function ($lq) use ($s) {
                      $lq->where('nombre', 'like', "%{$s}%");
                  });
            });
        }

        $anuncios = $query->orderBy('created_at', 'desc')->paginate(15);
            
        return response()->json($anuncios);
    }

    public function updateAnuncio(Request $request, $id)
    {
        $this->checkAdmin($request);

        $anuncio = \App\Models\Anuncio::findOrFail($id);
        
        $request->validate([
            'local_id' => 'nullable|exists:locales,id',
            'titulo' => 'sometimes|string|max:255',
            'descripcion' => 'nullable|string',
            'tipo' => 'sometimes|string',
            'estado' => 'sometimes|string',
            'en_slider' => 'nullable',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $data = $request->only(['titulo', 'descripcion', 'tipo', 'estado']);
        if ($request->exists('local_id')) {
            $data['local_id'] = $request->input('local_id') ?: null;
        }
        if ($request->has('en_slider')) {
            $data['en_slider'] = filter_var($request->input('en_slider'), FILTER_VALIDATE_BOOLEAN);
        }
        if ($request->boolean('quitar_imagen')) {
            $data['imagen'] = null;
        }
        if ($request->hasFile('imagen')) {
            $file = $request->file('imagen');
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('anuncios', $filename, 'public');
            $data['imagen'] = 'anuncios/' . $filename;
        }

        $anuncio->update($data);

        \Illuminate\Support\Facades\Cache::forget('marketplace_feed_home');

        return response()->json([
            'message' => 'Anuncio actualizado correctamente',
            'anuncio' => $anuncio->load('local:id,nombre,slug,foto_logo')
        ]);
    }

    public function deleteAnuncio(Request $request, $id)
    {
        $this->checkAdmin($request);

        $anuncio = \App\Models\Anuncio::findOrFail($id);
        $anuncio->delete();

        \Illuminate\Support\Facades\Cache::forget('marketplace_feed_home');

        return response()->json([
            'message' => 'Anuncio eliminado correctamente'
        ]);
    }

    public function deleteLocal(Request $request, $id)
    {
        $this->checkAdmin($request);

        $local = Local::findOrFail($id);
        
        DB::beginTransaction();
        try {
            // Optional: You could also delete the user if they only own this local.
            // But usually safely keeping the user is better, or implement logic later.
            $local->estado = 'inactivo';
            $local->save();

            DB::commit();
            return response()->json(['message' => 'Local desactivado correctamente']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al desactivar local', 'error' => $e->getMessage()], 500);
        }
    }

    public function dashboard(Request $request)
    {
        $this->checkAdmin($request);

        $totalLocales = DB::table('locales')->count();
        $localesActivos = DB::table('locales')->where('estado', 'activo')->count();
        $totalUsers = DB::table('users')->count();
        $totalPedidos = DB::table('pedidos')->count();
        $pedidosPendientes = DB::table('pedidos')->where('estado', 'pendiente')->count();

        $totalVistas = 0;
        $totalClicks = 0;
        try {
            if (\Illuminate\Support\Facades\Schema::hasColumn('locales', 'vistas_perfil')) {
                $totalVistas = (int) DB::table('locales')->sum('vistas_perfil');
                $totalClicks = (int) DB::table('locales')->sum('clicks_whatsapp');
            }
        } catch (\Throwable $e) {}

        $topBusquedas = [];
        $busquedasSinResultados = [];
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('busquedas_log')) {
                $topBusquedas = DB::table('busquedas_log')
                    ->orderBy('cantidad', 'desc')
                    ->take(10)
                    ->get();
                $busquedasSinResultados = DB::table('busquedas_log')
                    ->where('ultimo_resultado_count', 0)
                    ->orderBy('cantidad', 'desc')
                    ->take(10)
                    ->get();
            }
        } catch (\Throwable $e) {}

        return response()->json([
            'total_locales' => $totalLocales,
            'locales_activos' => $localesActivos,
            'total_usuarios' => $totalUsers,
            'total_pedidos' => $totalPedidos,
            'pedidos_pendientes' => $pedidosPendientes,
            'total_vistas_perfil' => $totalVistas,
            'total_clicks_whatsapp' => $totalClicks,
            'top_busquedas' => $topBusquedas,
            'busquedas_sin_resultados' => $busquedasSinResultados,
        ]);
    }

    public function updateLocal(Request $request, $id)
    {
        $this->checkAdmin($request);

        $local = Local::findOrFail($id);

        $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'telefono' => 'nullable|string|max:100',
            'direccion' => 'nullable|string|max:255',
            'localidad' => 'nullable|string|max:100',
            'descripcion' => 'nullable|string',
            'horario' => 'nullable|string',
            'estado' => 'sometimes|string|in:activo,inactivo,pendiente',
            'destacado' => 'sometimes',
            'plan' => 'sometimes|string',
            'plan_id' => 'sometimes|integer|exists:planes,id',
            'mostrar_feed' => 'sometimes',
            'tipo' => 'sometimes|string|in:venta,servicio,mixto,gym,financiera,farmacia,remiseria,info',
            'siempre_abierto' => 'sometimes',
            'de_turno' => 'sometimes',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'portada' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        if ($request->has('nombre') && !empty($request->nombre)) {
            $local->nombre = $request->nombre;
        }
        if ($request->has('telefono')) $local->telefono = $request->telefono;
        if ($request->has('direccion')) $local->direccion = $request->direccion;
        if ($request->has('localidad')) $local->localidad = $request->localidad;
        if ($request->has('descripcion')) $local->descripcion = $request->descripcion;
        if ($request->has('horario')) $local->horario = $request->horario;
        if ($request->has('instagram')) {
            $redesActuales = is_string($local->redes_sociales) ? (json_decode($local->redes_sociales, true) ?: []) : (is_array($local->redes_sociales) ? $local->redes_sociales : []);
            $redesActuales['instagram'] = trim((string) $request->input('instagram'));
            $local->redes_sociales = json_encode($redesActuales);
        } elseif ($request->has('redes_sociales')) {
            $local->redes_sociales = is_array($request->redes_sociales) ? json_encode($request->redes_sociales) : $request->redes_sociales;
        }

        if ($request->has('estado')) $local->estado = $request->estado;
        if ($request->has('destacado')) $local->destacado = filter_var($request->destacado, FILTER_VALIDATE_BOOLEAN);
        if ($request->has('plan')) {
            $local->plan = $request->plan;
            $planModel = \App\Models\Plan::where('slug', $request->plan)->orWhere('nombre', $request->plan)->first();
            if ($planModel) {
                $local->plan_id = $planModel->id;
            }
        }
        if ($request->has('plan_id')) $local->plan_id = $request->plan_id;
        if ($request->has('mostrar_feed')) $local->mostrar_feed = filter_var($request->mostrar_feed, FILTER_VALIDATE_BOOLEAN);
        if ($request->has('tipo')) $local->tipo = $request->tipo;
        if ($request->has('siempre_abierto')) $local->siempre_abierto = filter_var($request->siempre_abierto, FILTER_VALIDATE_BOOLEAN);
        if ($request->has('de_turno')) {
            $nuevoDeTurno = filter_var($request->de_turno, FILTER_VALIDATE_BOOLEAN);
            $local->de_turno = $nuevoDeTurno;
            if (\Illuminate\Support\Facades\Schema::hasColumn('locales', 'turno_fecha')) {
                $local->turno_fecha = Local::fechaTurnoActual();
            }
        }

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('locales/logos', $filename, 'public');
            $local->foto_logo = 'locales/logos/' . $filename;
        }

        if ($request->hasFile('portada')) {
            $file = $request->file('portada');
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('locales/portadas', $filename, 'public');
            $local->foto_portada = 'locales/portadas/' . $filename;
        }

        $local->save();

        if ($request->has('categories')) {
            $local->categories()->sync($this->parseIdArray($request->input('categories')));
        }
        if ($request->has('subcategories')) {
            $local->subcategories()->sync($this->parseIdArray($request->input('subcategories')));
        }

        \Illuminate\Support\Facades\Cache::forget('marketplace_feed_home');
        Local::limpiarCacheFarmaciasTurno();

        return response()->json([
            'message' => 'Local actualizado correctamente',
            'local' => $local->load(['categories', 'subcategories'])
        ]);
    }

    public function getUsuarios(Request $request)
    {
        $this->checkAdmin($request);

        $query = User::with(['role']);

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $perPage = min((int) $request->input('per_page', 15), 200);
        $paginator = $query->orderBy('created_at', 'desc')->paginate($perPage);

        $formatted = $paginator->getCollection()->map(function ($u) {
            $local = Local::where('id_user', $u->id)->first();
            $activeSessions = \Laravel\Sanctum\PersonalAccessToken::where('tokenable_id', $u->id)->count();
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role_id' => $u->role_id,
                'role_name' => $u->role ? $u->role->name : 'user',
                'local_nombre' => $local ? $local->nombre : null,
                'active_sessions' => $activeSessions,
                'created_at' => $u->created_at,
            ];
        });

        return response()->json([
            'usuarios' => $formatted,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage()
            ]
        ]);
    }

    public function updateUsuario(Request $request, $id)
    {
        $this->checkAdmin($request);

        $user = User::findOrFail($id);

        $request->validate([
            'role_id' => 'sometimes|integer|exists:roles,id',
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'password' => 'sometimes|nullable|string|min:6',
            'revoke_sessions' => 'sometimes|boolean',
        ]);

        if ($request->has('role_id')) $user->role_id = $request->role_id;
        if ($request->has('name')) $user->name = $request->name;
        if ($request->has('email')) $user->email = $request->email;

        $revokedCount = 0;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
            $revokedCount = \Laravel\Sanctum\PersonalAccessToken::where('tokenable_id', $user->id)->delete();
        } elseif ($request->boolean('revoke_sessions')) {
            $revokedCount = \Laravel\Sanctum\PersonalAccessToken::where('tokenable_id', $user->id)->delete();
        }

        $user->save();
        $user->load('role');

        return response()->json([
            'message' => $request->filled('password')
                ? "Contraseña actualizada y {$revokedCount} sesiones cerradas"
                : ($request->boolean('revoke_sessions')
                    ? "Se cerraron {$revokedCount} sesiones activas del usuario"
                    : 'Usuario actualizado correctamente'),
            'revoked_sessions' => $revokedCount,
            'user' => $user
        ]);
    }

    public function getCategorias(Request $request)
    {
        $this->checkAdmin($request);

        $query = \App\Models\Category::with('subcategories');
        
        if ($request->has('search') && !empty($request->search)) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $perPage = (int) $request->input('per_page', 50);
        $paginator = $query->orderBy('orden')->paginate($perPage);

        return response()->json([
            'categorias' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage()
            ]
        ]);
    }

    public function storeCategoria(Request $request)
    {
        $this->checkAdmin($request);

        $request->validate([
            'name'  => 'required|string|max:255',
            'icon'  => 'nullable|string|max:100',
            'orden' => 'nullable|integer',
            'state' => 'nullable|string',
        ]);

        $categoria = new \App\Models\Category();
        $categoria->name  = $request->name;
        $categoria->slug  = Str::slug($request->name);
        $categoria->icon  = $request->icon ?? null;
        $state = $request->state ?? 'active';
        if ($state === 'activo') $state = 'active';
        $categoria->state = $state;
        $categoria->orden = $request->orden ?? 0;
        $categoria->save();

        \Illuminate\Support\Facades\Cache::forget('marketplace_feed_home');

        return response()->json([
            'message'   => 'Categoría creada correctamente',
            'categoria' => $categoria->load('subcategories')
        ], 201);
    }

    public function updateCategoria(Request $request, $id)
    {
        $this->checkAdmin($request);

        $categoria = \App\Models\Category::findOrFail($id);

        $request->validate([
            'name'  => 'sometimes|string|max:255',
            'icon'  => 'nullable|string|max:100',
            'state' => 'sometimes|string',
            'orden' => 'sometimes|integer',
        ]);

        if ($request->has('name')) {
            $categoria->name = $request->name;
            $categoria->slug = Str::slug($request->name);
        }
        if ($request->has('icon'))  $categoria->icon  = $request->icon;
        if ($request->has('state')) {
            $state = $request->state;
            if ($state === 'activo') $state = 'active';
            $categoria->state = $state;
        }
        if ($request->has('orden')) $categoria->orden = $request->orden;

        $categoria->save();

        \Illuminate\Support\Facades\Cache::forget('marketplace_feed_home');

        return response()->json([
            'message'   => 'Categoría actualizada correctamente',
            'categoria' => $categoria
        ]);
    }

    public function deleteCategoria(Request $request, $id)
    {
        $this->checkAdmin($request);

        $categoria = \App\Models\Category::findOrFail($id);
        // Soft delete or mark as inactive
        $categoria->state = 'inactive';
        $categoria->save();

        \Illuminate\Support\Facades\Cache::forget('marketplace_feed_home');

        return response()->json([
            'message' => 'Categoría desactivada correctamente'
        ]);
    }

    public function storeSubcategoria(Request $request, $id)
    {
        $this->checkAdmin($request);

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $categoria = \App\Models\Category::findOrFail($id);
        $slug = \Illuminate\Support\Str::slug($request->name);

        $sub = \App\Models\Subcategory::create([
            'category_id' => $categoria->id,
            'name' => $request->name,
            'slug' => $slug
        ]);

        \Illuminate\Support\Facades\Cache::forget('marketplace_feed_home');

        return response()->json([
            'message' => 'Subcategoría creada correctamente',
            'subcategoria' => $sub
        ], 201);
    }

    public function deleteSubcategoria(Request $request, $id)
    {
        $this->checkAdmin($request);

        $sub = \App\Models\Subcategory::findOrFail($id);
        $sub->delete();

        \Illuminate\Support\Facades\Cache::forget('marketplace_feed_home');

        return response()->json([
            'message' => 'Subcategoría eliminada correctamente'
        ]);
    }


    public function getPlanes(Request $request)
    {
        $this->checkAdmin($request);
        
        $query = \App\Models\Plan::query();
        
        if ($request->has('search') && !empty($request->search)) {
            $query->where('nombre', 'like', '%' . $request->search . '%');
        }

        $perPage = (int) $request->input('per_page', 50);
        $paginator = $query->orderBy('orden')->paginate($perPage);
        
        return response()->json([
            'planes' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage()
            ]
        ]);
    }

    public function storePlan(Request $request)
    {
        $this->checkAdmin($request);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'precio_mensual' => 'required|numeric|min:0',
            'max_productos' => 'nullable|integer|min:1',
        ]);

        $plan = new \App\Models\Plan();
        $plan->nombre = $request->nombre;
        $plan->slug = Str::slug($request->nombre);
        $plan->precio_mensual = $request->precio_mensual;
        $plan->max_productos = $request->max_productos;
        $plan->tiene_pos = $request->tiene_pos ?? false;
        $plan->tiene_clientes = $request->tiene_clientes ?? false;
        $plan->tiene_caja = $request->tiene_caja ?? false;
        $plan->tiene_creditos = $request->tiene_creditos ?? false;
        $plan->tiene_reportes = $request->tiene_reportes ?? false;
        $plan->destacable = $request->destacable ?? false;
        $plan->recibe_pedidos = $request->recibe_pedidos ?? true;
        $plan->descripcion = $request->descripcion;
        $plan->estado = $request->estado ?? true;
        $plan->orden = $request->orden ?? 0;
        $plan->save();

        return response()->json([
            'message' => 'Plan creado correctamente',
            'plan' => $plan
        ], 201);
    }

    public function updatePlan(Request $request, $id)
    {
        $this->checkAdmin($request);
        $plan = \App\Models\Plan::findOrFail($id);

        $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'precio_mensual' => 'sometimes|numeric|min:0',
            'max_productos' => 'nullable|integer|min:1',
        ]);

        $fillable = [
            'nombre', 'precio_mensual', 'max_productos', 'tiene_pos', 'tiene_clientes',
            'tiene_caja', 'tiene_creditos', 'tiene_reportes', 'destacable', 'recibe_pedidos',
            'descripcion', 'estado', 'orden'
        ];

        foreach ($fillable as $field) {
            if ($request->has($field)) {
                $plan->$field = $request->$field;
            }
        }
        
        if ($request->has('nombre')) {
            $plan->slug = Str::slug($request->nombre);
        }

        $plan->save();

        return response()->json([
            'message' => 'Plan actualizado correctamente',
            'plan' => $plan
        ]);
    }

    public function deletePlan(Request $request, $id)
    {
        $this->checkAdmin($request);
        $plan = \App\Models\Plan::findOrFail($id);
        $plan->estado = false;
        $plan->save();

        return response()->json([
            'message' => 'Plan desactivado correctamente'
        ]);
    }
}
