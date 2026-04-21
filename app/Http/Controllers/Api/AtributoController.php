<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Atributo;
use App\Models\AtributoValor;
use App\Models\Local;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AtributoController extends Controller
{
    private function getLocalId(): ?int
    {
        $user = Auth::user();
        if (!$user) return null;
        $local = Local::where('id_user', $user->id)->first();
        return $local ? (int)$local->id : null;
    }

    /**
     * GET /api/atributos
     * Lista atributos del local con sus valores
     */
    public function index()
    {
        $localId = $this->getLocalId();
        if (!$localId) {
            return response()->json(['message' => 'No tenés un local asignado.'], 403);
        }

        // Broaden the search by including attributes that might have null state if they belong to this local
        $atributos = Atributo::with('valores')
            ->where('id_local', $localId)
            ->where(function($query) {
                $query->where('estado', 'activo')
                      ->orWhereNull('estado');
            })
            ->get();

        return response()->json(['attributes' => $atributos]);
    }

    /**
     * POST /api/atributos
     * Crea un atributo y sus valores
     */
    public function store(Request $request)
    {
        $localId = $this->getLocalId();
        if (!$localId) {
            return response()->json(['message' => 'No tenés un local asignado.'], 403);
        }

        $request->validate([
            'nombre'  => 'required|string|max:255',
            'tipo'    => 'nullable|string|in:color,talla,texto,numero,select',
            'valores' => 'nullable|array',
            'valores.*' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $atributo = Atributo::create([
                'nombre'      => $request->nombre,
                'tipo'        => $request->tipo ?? 'texto',
                'id_local'    => $localId,
                'estado'      => $request->estado ?? 'activo',
                'obligatorio' => $request->obligatorio ?? false,
                'orden'       => $request->orden ?? 0,
            ]);

            if ($request->has('valores') && is_array($request->valores)) {
                foreach ($request->valores as $index => $valor) {
                    AtributoValor::create([
                        'id_atributo' => $atributo->id_atributo,
                        'valor'       => $valor,
                        'orden'       => $index,
                        'estado'      => 'activo',
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message'   => 'Atributo creado exitosamente',
                'attribute' => $atributo->load('valores'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al crear atributo', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/atributos/{id}/valores
     * Añade valores a un atributo existente
     */
    public function storeValor(Request $request, $id)
    {
        $localId = $this->getLocalId();
        if (!$localId) {
            return response()->json(['message' => 'No tenés un local asignado.'], 403);
        }

        $atributo = Atributo::where('id_local', $localId)->findOrFail($id);

        $request->validate([
            'valor' => 'required|string',
        ]);

        $maxOrden = \App\Models\AtributoValor::where('id_atributo', $atributo->id_atributo)->max('orden') ?? 0;

        $valor = AtributoValor::create([
            'id_atributo' => $atributo->id_atributo,
            'valor'       => $request->valor,
            'orden'       => $maxOrden + 1,
            'estado'      => 'activo',
        ]);

        return response()->json([
            'message' => 'Valor añadido',
            'value'   => $valor,
        ], 201);
    }
}
