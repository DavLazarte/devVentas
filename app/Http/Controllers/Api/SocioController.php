<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SocioController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $localId = Auth::user()->local->id;

        $tipoPersona = $request->query('tipo_persona', 'cliente');

        $query = Persona::with(['user.role', 'membresias.plan'])
            ->where('tipo_persona', $tipoPersona);

        // Filtrar por local
        if ($localId) {
            $query->where('id_local', $localId);
        } else {
            // Seguridad: Si no hay local y no es SuperAdmin, devolver lista vacía
            if ($user->role_id !== 1) {
                return response()->json(['socios' => []]);
            }
        }

        // Filtros
        if ($request->has('estado')) {
            // Implementar filtro según estado de membresía
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('mail', 'like', "%{$search}%")
                    ->orWhere('telefono', 'like', "%{$search}%");
            });
        }

        $socios = $query->get()->map(function ($socio) {
            $membresiaActiva = $socio->membresia_activa;

            return [
                'id' => $socio->idpersona,
                'nombre' => $socio->nombre,
                'email' => $socio->mail,
                'telefono' => $socio->telefono,
                'direccion' => $socio->direccion,
                'fechaNacimiento' => $socio->fecha_nacimiento?->format('Y-m-d'),
                'foto' => $socio->foto,
                'dni' => $socio->dni_cuit,
                'estado' => $socio->tipo_persona === 'cliente' ? $socio->estado_membresia : $socio->estado,
                'planNombre' => $membresiaActiva?->plan->nombre ?? 'Sin plan',
                'planId' => $membresiaActiva?->idservicio,
                'fechaVencimiento' => $membresiaActiva?->fecha_fin?->format('Y-m-d'),
                'tieneUsuario' => !!$socio->user_id,
                'userId' => $socio->user_id,
                'tipo_membresia' => $membresiaActiva?->tipo,
                'creditos_restantes' => $membresiaActiva?->creditos_restantes,
            ];
        });

        return response()->json(['socios' => $socios]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:personas,mail',
            'telefono' => 'required|string',
            'direccion' => 'nullable|string',
            'fechaNacimiento' => 'nullable|date',
            'dni' => 'nullable|string',
            'foto' => 'nullable|string',
            'crearUsuario' => 'boolean',
            'password' => 'required_if:crearUsuario,true|min:8',
            'tipo_persona' => 'sometimes|string|in:cliente,instructor',
        ]);

        DB::beginTransaction();
        try {
            $localId = Auth::user()->local->id; // Default a 1 si no tiene persona
            $tipoPersona = $request->input('tipo_persona', 'cliente');
            // FIX: Capturar estado del request (parametro opcional) para instructores
            $estadoInicial = $request->input('estado', 'activo');

            $userId = null;
            if ($request->crearUsuario) {
                // Determine role based on tipo_persona
                // 6 = gym_socio (default), need to know ID for instructor if different
                // For now, assume same role or handle later
                $roleId = 6;

                $newUser = User::create([
                    'name' => $validated['nombre'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'role_id' => $roleId,
                ]);
                $userId = $newUser->id;
            }

            $socio = Persona::create([
                'tipo_persona' => $tipoPersona,
                'nombre' => $validated['nombre'],
                'mail' => $validated['email'],
                'telefono' => $validated['telefono'],
                'direccion' => $validated['direccion'] ?? null,
                'fecha_nacimiento' => $validated['fechaNacimiento'] ?? null,
                'dni_cuit' => $validated['dni'] ?? null,
                'foto' => $validated['foto'] ?? null,
                'user_id' => $userId,
                'estado' => $estadoInicial,
                'id_local' => $localId,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Socio creado exitosamente',
                'socio' => $socio,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al crear socio',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Obtener un socio
    public function show($id)
    {
        $socio = Persona::where('id_local', Auth::user()->local->id)
            ->with(['user.role', 'membresias.plan'])
            ->findOrFail($id);

        return response()->json([
            'socio' => [
                'id' => $socio->idpersona,
                'nombre' => $socio->nombre,
                'email' => $socio->mail,
                'telefono' => $socio->telefono,
                'direccion' => $socio->direccion,
                'fechaNacimiento' => $socio->fecha_nacimiento?->format('Y-m-d'),
                'foto' => $socio->foto,
                'dni' => $socio->dni_cuit,
                'tieneUsuario' => !!$socio->user_id,
                'usuario' => $socio->user ? [
                    'id' => $socio->user->id,
                    'email' => $socio->user->email,
                    'role' => $socio->user->role->name ?? null,
                ] : null,
            ]
        ]);
    }

    // Actualizar socio
    public function update(Request $request, $id)
    {
        $socio = Persona::where('id_local', Auth::user()->local->id)->findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:personas,mail,' . $id . ',idpersona',
            'telefono' => 'sometimes|string',
            'direccion' => 'nullable|string',
            'fechaNacimiento' => 'nullable|date',
            'dni' => 'nullable|string',
            'foto' => 'nullable|string',
            'estado' => 'sometimes|string|in:activo,inactivo',
        ]);

        $socio->update([
            'nombre' => $validated['nombre'] ?? $socio->nombre,
            'mail' => $validated['email'] ?? $socio->mail,
            'telefono' => $validated['telefono'] ?? $socio->telefono,
            'direccion' => $validated['direccion'] ?? $socio->direccion,
            'fecha_nacimiento' => $validated['fechaNacimiento'] ?? $socio->fecha_nacimiento,

            'dni_cuit' => $validated['dni'] ?? $socio->dni_cuit,
            'foto' => $validated['foto'] ?? $socio->foto,
            'estado' => $validated['estado'] ?? $socio->estado,
        ]);

        return response()->json([
            'message' => 'Socio actualizado exitosamente',
            'socio' => $socio,
        ]);
    }

    // Eliminar socio
    public function destroy($id)
    {
        $socio = Persona::where('id_local', Auth::user()->local->id)->findOrFail($id);
        $socio->delete();

        return response()->json([
            'message' => 'Socio eliminado exitosamente'
        ]);
    }

    // Crear usuario para socio existente
    public function createUser(Request $request, $id)
    {
        $socio = Persona::findOrFail($id);

        if ($socio->user_id) {
            return response()->json([
                'message' => 'Este socio ya tiene un usuario asignado'
            ], 400);
        }

        $validated = $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
        ]);

        $user = User::create([
            'name' => $socio->nombre,
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => 6, // gym_socio
        ]);

        $socio->update(['user_id' => $user->id]);

        return response()->json([
            'message' => 'Usuario creado exitosamente',
            'user' => $user,
        ], 201);
    }
}
