<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PagoGym;
use App\Models\Persona;
use App\Models\Membresia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PagoGymController extends Controller
{
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
        $localId = $this->getLocalId();
        $query = PagoGym::where('id_local', $localId)
            ->with(['persona', 'membresia.plan'])
            ->orderBy('fecha_pago', 'desc');

        if ($request->has('id_persona')) {
            $query->where('idpersona', $request->id_persona);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('persona', function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%");
            });
        }

        $perPage = $request->input('per_page', 15);
        $pagos = $query->paginate($perPage);

        $deudaTotal = Membresia::where('id_local', $localId)
            ->whereIn('estado', ['activa', 'por_vencer'])
            ->get()
            ->sum(function ($m) {
                return $m->saldo_pendiente;
            });

        return response()->json([
            'pagos' => $pagos->items(),
            'meta' => [
                'current_page' => $pagos->currentPage(),
                'last_page' => $pagos->lastPage(),
                'per_page' => $pagos->perPage(),
                'total' => $pagos->total(),
            ],
            'deuda_total' => $deudaTotal
        ]);
    }

    public function store(Request $request)
    {
        $localId = $this->getLocalId();
        $user = Auth::user();

        $validated = $request->validate([
            'id_persona' => 'required|exists:personas,idpersona',
            'id_membresia' => 'required|exists:membresias,id',
            'monto' => 'required|numeric|min:0',
            'metodo_pago' => 'required|string',
            'fecha_pago' => 'required|date',
            'observaciones' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $pago = PagoGym::create([
                'idpersona' => $validated['id_persona'],
                'id_membresia' => $validated['id_membresia'],
                'id_local' => $localId,
                'id_user' => $user->id,
                'monto' => $validated['monto'],
                'metodo_pago' => $validated['metodo_pago'],
                'fecha_pago' => $validated['fecha_pago'],
                'observaciones' => $validated['observaciones'] ?? null,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Pago registrado correctamente',
                'pago' => $pago->load(['persona', 'membresia.plan'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al registrar el pago',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $localId = $this->getLocalId();
        $pago = PagoGym::where('id_local', $localId)
            ->with(['persona', 'membresia.plan'])
            ->findOrFail($id);

        return response()->json(['pago' => $pago]);
    }

    public function destroy($id)
    {
        $localId = $this->getLocalId();
        $pago = PagoGym::where('id_local', $localId)->findOrFail($id);
        $pago->delete();

        return response()->json([
            'message' => 'Pago eliminado correctamente'
        ]);
    }
}
