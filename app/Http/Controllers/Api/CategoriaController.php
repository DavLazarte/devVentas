<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Local;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CategoriaController extends Controller
{
    private function getLocalId(): ?int
    {
        $user = Auth::user();
        if (!$user) return null;

        $local = Local::where('id_user', $user->id)->first();
        return $local ? $local->id : null;
    }

    /**
     * GET /api/categorias
     * Obtiene el listado de categorías activas para el local
     */
    public function index(Request $request)
    {
        $localId = $this->getLocalId();
        if (!$localId) {
            return response()->json(['message' => 'Sin local'], 403);
        }

        $categorias = Categoria::where('id_local', $localId)
            ->where('estado', 'activo')
            ->get(['id_categoria', 'nombre']);

        return response()->json(['categories' => $categorias]);
    }

    /**
     * POST /api/categorias
     * Crea una categoría para el local
     */
    public function store(Request $request)
    {
        $localId = $this->getLocalId();
        if (!$localId) {
            return response()->json(['message' => 'Sin local activo'], 403);
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        $categoria = Categoria::firstOrCreate(
            ['nombre' => $request->nombre, 'id_local' => $localId],
            ['estado' => 'activo']
        );

        return response()->json([
            'message'  => 'Categoría creada',
            'category' => $categoria
        ], 201);
    }
}
