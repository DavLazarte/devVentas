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

        $locales = Local::with(['user:id,name,email,role_id'])->orderBy('created_at', 'desc')->get();

        $formateado = $locales->map(function ($l) {
            return [
                'id' => $l->id,
                'nombre' => $l->nombre,
                'slug' => $l->slug,
                'email' => $l->email,
                'telefono' => $l->telefono,
                'estado' => $l->estado,
                'plan' => $l->plan,
                'tipo' => $l->tipo,
                'owner' => $l->user ? [
                    'id' => $l->user->id,
                    'name' => $l->user->name,
                    'email' => $l->user->email,
                ] : null,
                'created_at' => $l->created_at,
            ];
        });

        return response()->json(['locales' => $formateado]);
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
            return response()->json(['message' => 'Error al crear el inquilino', 'error' => $e->getMessage()], 500);
        }
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
}
