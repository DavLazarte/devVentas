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

        $query = Local::with(['user:id,name,email,role_id']);

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($qu) use ($search) {
                      $qu->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $paginator = $query->orderBy('created_at', 'desc')->paginate(15);

        $formateado = $paginator->getCollection()->map(function ($l) {
            return [
                'id' => $l->id,
                'nombre' => $l->nombre,
                'slug' => $l->slug,
                'email' => $l->email,
                'telefono' => $l->telefono,
                'estado' => $l->estado,
                'plan' => $l->plan,
                'tipo' => $l->tipo,
                'destacado' => (bool) $l->destacado,
                'mostrar_feed' => (bool) $l->mostrar_feed,
                'siempre_abierto' => (bool) $l->siempre_abierto,
                'de_turno' => (bool) $l->de_turno,
                'foto_logo_url' => $l->foto_logo_url,
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

    public function storePublicLocal(Request $request)
    {
        $this->checkAdmin($request);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|string',
            'telefono' => 'nullable|string',
            'direccion' => 'nullable|string',
            'descripcion' => 'nullable|string'
        ]);

        $slug = Str::slug($request->nombre);
        $originalSlug = $slug;
        $count = 1;
        while(Local::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        $local = Local::create([
            'id_user' => $request->user()->id, // Propiedad temporal del admin
            'nombre' => $request->nombre,
            'slug' => $slug,
            'estado' => 'activo',
            'plan' => 'free',
            'tipo' => $request->tipo,
            'mostrar_feed' => true,
            'telefono' => $request->telefono,
            'direccion' => $request->direccion,
            'descripcion' => $request->descripcion
        ]);

        return response()->json([
            'message' => 'Local público creado con éxito',
            'local' => $local
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
        ]);

        $local = Local::findOrFail($id);

        $anuncio = \App\Models\Anuncio::create([
            'local_id' => $local->id,
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'tipo' => $request->tipo,
            'estado' => 'activo'
        ]);

        return response()->json([
            'message' => 'Anuncio creado correctamente',
            'anuncio' => $anuncio
        ], 201);
    }

    public function storeGlobalAnuncio(Request $request)
    {
        $this->checkAdmin($request);

        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'tipo' => 'required|string',
        ]);

        $anuncio = \App\Models\Anuncio::create([
            'local_id' => null, // Global!
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'tipo' => $request->tipo,
            'estado' => 'activo'
        ]);

        return response()->json([
            'message' => 'Anuncio global creado correctamente',
            'anuncio' => $anuncio
        ], 201);
    }

    public function getAnuncios(Request $request)
    {
        $this->checkAdmin($request);
        
        $anuncios = \App\Models\Anuncio::with('local:id,nombre')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        return response()->json($anuncios);
    }

    public function updateAnuncio(Request $request, $id)
    {
        $this->checkAdmin($request);

        $anuncio = \App\Models\Anuncio::findOrFail($id);
        
        $request->validate([
            'titulo' => 'sometimes|string|max:255',
            'descripcion' => 'nullable|string',
            'tipo' => 'sometimes|string',
            'estado' => 'sometimes|string'
        ]);

        $anuncio->update($request->only(['titulo', 'descripcion', 'tipo', 'estado']));

        return response()->json([
            'message' => 'Anuncio actualizado correctamente',
            'anuncio' => $anuncio
        ]);
    }

    public function deleteAnuncio(Request $request, $id)
    {
        $this->checkAdmin($request);

        $anuncio = \App\Models\Anuncio::findOrFail($id);
        $anuncio->delete();

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

        return response()->json([
            'total_locales' => $totalLocales,
            'locales_activos' => $localesActivos,
            'total_usuarios' => $totalUsers,
            'total_pedidos' => $totalPedidos,
            'pedidos_pendientes' => $pedidosPendientes,
        ]);
    }

    public function updateLocal(Request $request, $id)
    {
        $this->checkAdmin($request);

        $local = Local::findOrFail($id);

        $request->validate([
            'estado' => 'sometimes|string|in:activo,inactivo,pendiente',
            'destacado' => 'sometimes|boolean',
            'plan' => 'sometimes|string',
            'plan_id' => 'sometimes|integer|exists:planes,id',
            'mostrar_feed' => 'sometimes|boolean',
            'tipo' => 'sometimes|string|in:venta,servicio,gym,financiera,farmacia',
            'siempre_abierto' => 'sometimes|boolean',
            'de_turno' => 'sometimes|boolean',
        ]);

        if ($request->has('estado')) $local->estado = $request->estado;
        if ($request->has('destacado')) $local->destacado = $request->destacado;
        if ($request->has('plan')) {
            $local->plan = $request->plan;
            $planModel = \App\Models\Plan::where('slug', $request->plan)->orWhere('nombre', $request->plan)->first();
            if ($planModel) {
                $local->plan_id = $planModel->id;
            }
        }
        if ($request->has('plan_id')) $local->plan_id = $request->plan_id;
        if ($request->has('mostrar_feed')) $local->mostrar_feed = $request->mostrar_feed;
        if ($request->has('tipo')) $local->tipo = $request->tipo;
        if ($request->has('siempre_abierto')) $local->siempre_abierto = $request->siempre_abierto;
        if ($request->has('de_turno')) $local->de_turno = $request->de_turno;

        $local->save();

        return response()->json([
            'message' => 'Local actualizado correctamente',
            'local' => $local
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

        $paginator = $query->orderBy('created_at', 'desc')->paginate(15);

        $formatted = $paginator->getCollection()->map(function ($u) {
            $local = Local::where('id_user', $u->id)->first();
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role_id' => $u->role_id,
                'role_name' => $u->role ? $u->role->name : 'user',
                'local_nombre' => $local ? $local->nombre : null,
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
        ]);

        if ($request->has('role_id')) $user->role_id = $request->role_id;
        if ($request->has('name')) $user->name = $request->name;
        if ($request->has('email')) $user->email = $request->email;

        $user->save();
        $user->load('role');

        return response()->json([
            'message' => 'Usuario actualizado correctamente',
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

        $paginator = $query->orderBy('orden')->paginate(15);

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
        $categoria->state = $request->state ?? 'activo';
        $categoria->orden = $request->orden ?? 0;
        $categoria->save();

        return response()->json([
            'message'   => 'Categoría creada correctamente',
            'categoria' => $categoria
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
        if ($request->has('state')) $categoria->state = $request->state;
        if ($request->has('orden')) $categoria->orden = $request->orden;

        $categoria->save();

        return response()->json([
            'message'   => 'Categoría actualizada correctamente',
            'categoria' => $categoria
        ]);
    }

    public function deleteCategoria(Request $request, $id)
    {
        $this->checkAdmin($request);

        $categoria = \App\Models\Category::findOrFail($id);
        // Soft delete or mark as inactivo
        $categoria->state = 'inactivo';
        $categoria->save();

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

        $paginator = $query->orderBy('orden')->paginate(15);
        
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
