<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Persona;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SocioController extends Controller
{
    /**
     * Obtener el ID del local actual de forma robusta.
     */
    private function getLocalId()
    {
        $user = Auth::user();
        if (!$user) return null;

        try {
            if ($user->local && isset($user->local->id)) {
                return $user->local->id;
            }
        } catch (\Exception $e) {
        }

        $persona = Persona::where('user_id', $user->id)->first();
        if ($persona) {
            return $persona->id_local;
        }

        return $user->id_local ?? $user->local_id ?? null;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $localId = $this->getLocalId();

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
                'foto' => $socio->foto ? (preg_match('/^http/', $socio->foto) ? $socio->foto : url($socio->foto)) : null,
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
                // Buscar el rol por nombre para evitar problemas con IDs diferentes en producción
                $gymSocioRole = Role::where('name', 'gym_socio')->first();

                if (!$gymSocioRole) {
                    throw new \Exception('Error: No se encontró el rol gym_socio en el sistema');
                }

                $newUser = User::create([
                    'name' => $validated['nombre'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'role_id' => $gymSocioRole->id,
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
                'foto' => $socio->foto ? (preg_match('/^http/', $socio->foto) ? $socio->foto : url($socio->foto)) : null,
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

    // Actualizar perfil del socio (desde el panel de cliente)
    public function updateProfile(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $socio = Persona::where('user_id', $user->id)->first();

        if (!$socio) {
            return response()->json(['message' => 'Perfil no encontrado'], 404);
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:personas,mail,' . $socio->idpersona . ',idpersona',
            'telefono' => 'nullable|string',
            'password' => 'nullable|min:8|confirmed',
            'foto' => 'nullable|string', // Acepta URL o base64
        ]);

        DB::beginTransaction();
        try {
            $fotoUrl = $socio->foto;
            if ($request->has('foto') && !empty($validated['foto'])) {
                $foto = $validated['foto'];
                // Verificar si es base64
                if (preg_match('/^data:image\/(\w+);base64,/', $foto, $type)) {
                    $foto = substr($foto, strpos($foto, ',') + 1);
                    $type = strtolower($type[1]); // jpg, png, gif

                    if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                        throw new \Exception('Tipo de imagen inválido. Solo jpg, jpeg, png, gif.');
                    }
                    $foto = base64_decode($foto);
                    if ($foto === false) {
                        throw new \Exception('Error al decodificar la imagen base64.');
                    }

                    // Generar nombre archivo
                    $filename = 'perfiles/' . uniqid() . '.' . $type;

                    // Guardar en disco publico
                    Storage::disk('public')->put($filename, $foto);

                    // URL Proxy
                    $fotoUrl = url('/api/images/perfiles/' . basename($filename));
                } else {
                    $fotoUrl = $foto;
                }
            }

            // 1. Actualizar Persona (Socio)
            $socio->update([
                'nombre' => $validated['nombre'],
                'mail' => $validated['email'],
                'telefono' => $validated['telefono'],
                'foto' => $fotoUrl,
            ]);

            // 2. Actualizar Usuario asociado
            $user->name = $validated['nombre'];

            // Si cambió el email, verificar unicidad en users
            if ($user->email !== $validated['email']) {
                if (User::where('email', $validated['email'])->where('id', '!=', $user->id)->exists()) {
                    throw new \Exception('El email ya está en uso por otro usuario.');
                }
                $user->email = $validated['email'];
            }

            // Si envió password
            if (!empty($validated['password'])) {
                $user->password = Hash::make($validated['password']);
            }

            $user->save();

            DB::commit();

            // Refrescar el modelo para asegurar datos actuales
            $socio->refresh();
            // Transformar la foto a URL absoluta para la respuesta inmediata
            $socio->foto = $socio->foto ? (preg_match('/^http/', $socio->foto) ? $socio->foto : url($socio->foto)) : null;

            return response()->json(['message' => 'Perfil actualizado correctamente', 'socio' => $socio]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al actualizar perfil', 'error' => $e->getMessage()], 500);
        }
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
        $socio = Persona::where('id_local', $this->getLocalId())->findOrFail($id);
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

        // Buscar el rol por nombre para evitar problemas con IDs diferentes en producción
        $gymSocioRole = Role::where('name', 'gym_socio')->first();

        if (!$gymSocioRole) {
            return response()->json([
                'message' => 'Error: No se encontró el rol gym_socio en el sistema'
            ], 500);
        }

        $user = User::create([
            'name' => $socio->nombre,
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $gymSocioRole->id,
        ]);

        $socio->update(['user_id' => $user->id]);

        return response()->json([
            'message' => 'Usuario creado exitosamente',
            'user' => $user,
        ], 201);
    }

    // Obtener perfil del socio autenticado
    public function getProfile(Request $request)
    {
        $user = Auth::user();

        $socio = Persona::where('user_id', $user->id)
            ->with(['membresias.plan'])
            ->first();

        if (!$socio) {
            return response()->json([
                'message' => 'No se encontró el perfil de socio para este usuario.'
            ], 404);
        }

        $membresiaActiva = $socio->membresia_activa;

        return response()->json([
            'socio' => [
                'id' => $socio->idpersona,
                'nombre' => $socio->nombre,
                'email' => $socio->mail,
                'telefono' => $socio->telefono,
                'direccion' => $socio->direccion,
                'fechaNacimiento' => $socio->fecha_nacimiento?->format('Y-m-d'),
                'fechaNacimiento' => $socio->fecha_nacimiento?->format('Y-m-d'),
                'foto' => $socio->foto ? (preg_match('/^http/', $socio->foto) ? $socio->foto : url($socio->foto)) : null,
                'dni' => $socio->dni_cuit,
                'membresia' => $membresiaActiva ? [
                    'plan' => $membresiaActiva->plan->nombre,
                    'estado' => $socio->estado_membresia, // Usamos el calculado
                    'vencimiento' => $membresiaActiva->fecha_fin?->format('Y-m-d'),
                    'tipo' => $membresiaActiva->tipo,
                    'creditos' => $membresiaActiva->creditos_restantes,
                ] : null,
            ]
        ]);
    }

    public function serveImage($filename)
    {
        $path = storage_path('app/public/perfiles/' . $filename);
        if (!file_exists($path)) abort(404);
        return response()->file($path);
    }
}
