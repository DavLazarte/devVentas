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
        $local = Local::where('id_user', $user->id)->first() ?? Local::find($user->id_local);

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
            ],
            'token' => $token,
        ]);
    }

    public function user(Request $request)
    {
        $user = $request->user();
        $local = Local::where('id_user', $user->id)->first() ?? Local::find($user->id_local);

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

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            // Asignar rol por defecto si es necesario
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }
}
