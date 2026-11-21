<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Local;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales son incorrectas.'],
            ]);
        }

        // Obtener local del usuario (ya sea dueño o empleado)
        $local = Local::with('planInfo')->where('id_user', $user->id)->first() ?? Local::with('planInfo')->find($user->id_local);

        // Crear token Sanctum
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => [
                'id'        => $user->id,
                'name'      => $user->name,
                'email'     => $user->email,
                'role'      => $user->role_id,
                'role_name' => $user->role->name ?? null,
                'local_id'  => $local?->id,
                'local_nombre' => $local?->nombre,
                'local_tipo'   => $local?->tipo,
                'plan_info'    => $local?->planInfo,
            ],
            'token' => $token,
        ]);
    }

    public function user(Request $request)
    {
        $user = $request->user();
        $local = Local::with('planInfo')->where('id_user', $user->id)->first() ?? Local::with('planInfo')->find($user->id_local);

        return response()->json([
            'user' => [
                'id'           => $user->id,
                'name'         => $user->name,
                'email'        => $user->email,
                'role_id'      => $user->role_id,
                'role_name'    => $user->role->name ?? null,
                'local_id'     => $local?->id,
                'local_nombre' => $local?->nombre,
                'local_tipo'   => $local?->tipo,
                'plan_info'    => $local?->planInfo,
            ]
        ]);
    }

    public function logout(Request $request)
    {
        // Eliminar el token actual
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout exitoso'
        ]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'password.min' => 'La nueva contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['La contraseña actual es incorrecta.'],
            ]);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        // Revocar TODOS los tokens activos del usuario en todos los dispositivos
        $revokedCount = \Laravel\Sanctum\PersonalAccessToken::where('tokenable_id', $user->id)->delete();

        // Emitir un único token nuevo para el dispositivo actual
        $newToken = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Contraseña actualizada. Se cerraron todas las demás sesiones activas.',
            'token' => $newToken,
            'revoked_sessions' => $revokedCount,
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
        ], [
            'email.unique' => 'Este email ya está registrado. Si tenés cuenta en Vértice o tenés un POS, ingresá con los datos de esa cuenta.'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => 2, // 2 = user normal
        ]);

        $user->load('role');

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role_id' => $user->role_id,
                'role_name' => $user->role ? $user->role->name : 'user',
            ],
            'token' => $token,
        ], 201);
    }

    public function registrarNegocio(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'nombre_local' => 'required|string|max:255',
            'tipo_local' => 'required|in:venta,servicio,mixto',
        ], [
            'email.unique' => 'Este email ya está registrado. Si tenés cuenta en Vértice o tenés un POS, ingresá con los datos de esa cuenta.'
        ]);

        \DB::beginTransaction();

        try {
            // 1. Crear usuario (Dueño)
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_id' => 2, // Asumiendo que 2 es el rol de dueño/admin de local
            ]);

            // 2. Crear local
            $slug = \Str::slug($request->nombre_local);
            
            // Verificar si slug existe y hacerlo único
            $count = Local::where('slug', 'LIKE', "{$slug}%")->count();
            if ($count > 0) {
                $slug = $slug . '-' . ($count + 1);
            }

            $local = Local::create([
                'id_user' => $user->id,
                'nombre' => $request->nombre_local,
                'slug' => $slug,
                'tipo' => $request->tipo_local,
                'estado' => 'activo', // Por ahora lo dejamos activo automáticamente
                'mostrar_feed' => true,
                'plan' => 'free',
            ]);
            
            // Asignar categorías globales si las envían
            if ($request->has('categorias')) {
                $local->categories()->sync($request->categorias);
            }

            \DB::commit();

            $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([
                'user' => [
                    'id'        => $user->id,
                    'name'      => $user->name,
                    'email'     => $user->email,
                    'role'      => $user->role_id,
                    'local_id'  => $local->id,
                    'local_nombre' => $local->nombre,
                    'local_tipo'   => $local->tipo,
                    'local_slug'   => $local->slug,
                ],
                'token' => $token,
            ], 201);

        } catch (\Exception $e) {
            \DB::rollback();
            return response()->json(['error' => 'Error al registrar el negocio: ' . $e->getMessage()], 500);
        }
    }
}
