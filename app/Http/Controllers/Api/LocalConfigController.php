<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Local;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LocalConfigController extends Controller
{
    public function crearLocal(Request $request)
    {
        $user = $request->user();
        
        $existing = Local::where('id_user', $user->id)->first();
        if ($existing) {
            return response()->json(['message' => 'Ya tienes un local registrado'], 400);
        }

        $request->validate([
            'nombre_local' => 'required|string|max:255',
            'tipo_local' => 'required|in:venta,servicio,gym,financiera,farmacia',
        ]);

        $slug = Str::slug($request->nombre_local);
        
        $count = Local::where('slug', 'LIKE', "{$slug}%")->count();
        if ($count > 0) {
            $slug = $slug . '-' . ($count + 1);
        }

        $local = Local::create([
            'id_user' => $user->id,
            'nombre' => $request->nombre_local,
            'slug' => $slug,
            'tipo' => $request->tipo_local,
            'estado' => 'activo',
            'mostrar_feed' => true,
            'plan' => 'free',
            'plan_id' => \App\Models\Plan::where('slug', 'free')->value('id'),
        ]);
        
        // Cambiamos el rol a vendedor si era usuario normal
        if ($user->role_id == 2) {
            $rolVendedor = \App\Models\Role::where('name', 'vendedor')->first();
            if ($rolVendedor) {
                $user->role_id = $rolVendedor->id;
            } else {
                // fallback: buscamos el rol 3 (vendedor en la mayoría de los sistemas)
                $user->role_id = 3;
            }
            $user->save();
        }

        return response()->json([
            'message' => 'Negocio creado exitosamente',
            'local' => $local
        ], 201);
    }

    public function getPerfil(Request $request)
    {
        $local = Local::with(['planInfo', 'categories', 'subcategories'])->where('id_user', $request->user()->id)->firstOrFail();
        return response()->json($local);
    }

    public function updatePerfil(Request $request)
    {
        $local = Local::where('id_user', $request->user()->id)->firstOrFail();

        $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'descripcion' => 'nullable|string',
            'direccion' => 'nullable|string',
            'localidad' => 'nullable|string|max:100',
            'provincia' => 'nullable|string|max:100',
            'telefono' => 'nullable|string',
            'email' => 'nullable|email',
            'horario' => 'nullable|string',
            'sitio_web' => 'nullable|string',
            'redes_sociales' => 'nullable|array',
            'latitud' => 'nullable|numeric',
            'longitud' => 'nullable|numeric',
            'mostrar_feed' => 'nullable|boolean',
            'siempre_abierto' => 'nullable|boolean',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'subcategories' => 'nullable|array',
            'subcategories.*' => 'exists:subcategories,id',
        ]);

        if ($request->has('nombre')) $local->nombre = $request->nombre;
        if ($request->has('descripcion')) $local->descripcion = $request->descripcion;
        if ($request->has('direccion')) $local->direccion = $request->direccion;
        if ($request->has('localidad')) $local->localidad = $request->localidad;
        if ($request->has('provincia')) $local->provincia = $request->provincia;
        if ($request->has('telefono')) $local->telefono = $request->telefono;
        if ($request->has('email')) $local->email = $request->email;
        if ($request->has('horario')) $local->horario = $request->horario;
        if ($request->has('sitio_web')) $local->sitio_web = $request->sitio_web;
        if ($request->has('latitud')) $local->latitud = $request->latitud;
        if ($request->has('longitud')) $local->longitud = $request->longitud;
        if ($request->has('mostrar_feed')) $local->mostrar_feed = $request->mostrar_feed;
        if ($request->has('redes_sociales')) {
            $local->redes_sociales = is_array($request->redes_sociales) ? json_encode($request->redes_sociales) : $request->redes_sociales;
        }
        if ($request->has('siempre_abierto')) {
            $local->siempre_abierto = $request->siempre_abierto;
        }

        $local->save();

        if ($request->has('categories')) {
            $local->categories()->sync($request->categories);
        }
        
        if ($request->has('subcategories')) {
            $local->subcategories()->sync($request->subcategories);
        }

        return response()->json([
            'message' => 'Perfil actualizado correctamente',
            'local' => $local->load(['categories', 'subcategories'])
        ]);
    }

    public function uploadLogo(Request $request)
    {
        $local = Local::where('id_user', $request->user()->id)->firstOrFail();

        $request->validate([
            'logo' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public/locales/logos', $filename);

            $local->foto_logo = 'locales/logos/' . $filename;
            $local->save();

            return response()->json([
                'message' => 'Logo subido correctamente',
                'url' => asset('storage/locales/logos/' . $filename),
                'filename' => $filename
            ]);
        }

        return response()->json(['message' => 'No se subió ningún archivo'], 400);
    }

    public function uploadPortada(Request $request)
    {
        $local = Local::where('id_user', $request->user()->id)->firstOrFail();

        $request->validate([
            'portada' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('portada')) {
            $file = $request->file('portada');
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public/locales/portadas', $filename);

            $local->foto_portada = 'locales/portadas/' . $filename;
            $local->save();

            return response()->json([
                'message' => 'Portada subida correctamente',
                'url' => asset('storage/locales/portadas/' . $filename),
                'filename' => $filename
            ]);
        }

        return response()->json(['message' => 'No se subió ningún archivo'], 400);
    }
}
