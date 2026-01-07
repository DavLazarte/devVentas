<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Persona;
use App\Models\Membresia;
use App\Models\ReservaGym;
use App\Models\AsistenciaGym;
use App\Models\PagoGym;
use App\Models\ClaseGym;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
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

    public function getStats(Request $request)
    {
        $localId = $this->getLocalId();
        $today = Carbon::today();
        $now = Carbon::now();

        // 1. Métricas Clave
        $totalSociosActivos = Persona::where('id_local', $localId)
            ->where('tipo_persona', 'cliente')
            ->where('estado_membresia', 'activo')
            ->count();

        $clasesHoyCount = ClaseGym::where('id_local', $localId)
            ->where('estado', 'activa')
            ->count(); // Simplificado: todas las clases activas en general. Podría filtrarse por día de la semana.

        // Mejorar clases hoy: filtrar por día actual
        $dayOfWeekMap = [
            0 => 'Domingo',
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado'
        ];
        $todayName = $dayOfWeekMap[$today->dayOfWeek];
        $clasesHoyCount = ClaseGym::where('id_local', $localId)
            ->where('estado', 'activa')
            ->where('dias_semana', 'like', "%{$todayName}%")
            ->count();

        $reservasSemana = ReservaGym::whereHas('clase', function ($q) use ($localId) {
            $q->where('id_local', $localId);
        })
            ->whereBetween('fecha_reserva', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->count();

        $ingresosHoy = PagoGym::where('id_local', $localId)
            ->whereDate('fecha_pago', $today)
            ->sum('monto');

        // 2. Gráfico de Asistencia (Últimos 7 días)
        $asistenciaSemanal = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $count = AsistenciaGym::where('id_local', $localId)
                ->whereDate('fecha_asistencia', $date)
                ->count();

            $asistenciaSemanal[] = [
                'day' => $date->isoFormat('ddd'),
                'visits' => $count,
                'percentage' => 0 // Se calculará en el front o basado en un máximo
            ];
        }

        // Calcular porcentaje basado en el máximo de la semana para el gráfico
        $maxVisits = collect($asistenciaSemanal)->max('visits') ?: 1;
        foreach ($asistenciaSemanal as &$item) {
            $item['percentage'] = round(($item['visits'] / $maxVisits) * 100);
        }

        // 3. Clases Populares (Top 5 con más reservas/asistencias históricas o recientes)
        $clasesPopulares = ClaseGym::where('id_local', $localId)
            ->withCount('reservas')
            ->orderBy('reservas_count', 'desc')
            ->take(5)
            ->get()
            ->map(function ($clase) {
                return [
                    'name' => $clase->nombre,
                    'reservations' => $clase->reservas_count,
                    'trend' => '+0%' // Placeholder de tendencia
                ];
            });

        // 4. Estados de Membresía
        $membresiasStatus = [
            'activas' => Persona::where('id_local', $localId)->where('estado_membresia', 'activo')->count(),
            'por_vencer' => Persona::where('id_local', $localId)->where('estado_membresia', 'por_vencer')->count(),
            'vencidas' => Persona::where('id_local', $localId)->where('estado_membresia', 'vencido')->count(),
        ];

        // 5. Ocupación Actual (Basado en reservas de la hora actual vs cupo total)
        $currentClasses = ClaseGym::where('id_local', $localId)
            ->where('estado', 'activa')
            ->where('dias_semana', 'like', "%{$todayName}%")
            ->get();

        $totalCupos = $currentClasses->sum('cupo_maximo') ?: 1;
        $reservasAhora = ReservaGym::whereIn('id_clase_gym', $currentClasses->pluck('id'))
            ->whereDate('fecha_reserva', $today)
            // Podríamos filtrar por la hora de la clase que está ocurriendo "ahora"
            ->count();

        $ocupacionActual = round(($reservasAhora / $totalCupos) * 100);

        // 6. Instructores Activos Hoy
        $instructoresHoy = Persona::where('id_local', $localId)
            ->where('tipo_persona', 'instructor')
            ->whereHas('clasesCoach', function ($q) use ($todayName) {
                $q->where('estado', 'activa')
                    ->where('dias_semana', 'like', "%{$todayName}%");
            })
            ->withCount(['clasesCoach' => function ($q) use ($todayName) {
                $q->where('estado', 'activa')
                    ->where('dias_semana', 'like', "%{$todayName}%");
            }])
            ->get()
            ->map(function ($ins) {
                return [
                    'name' => $ins->nombre,
                    'classes' => $ins->clases_coach_count
                ];
            });

        return response()->json([
            'metrics' => [
                'sociosActivos' => $totalSociosActivos,
                'clasesHoy' => $clasesHoyCount,
                'reservasSemana' => $reservasSemana,
                'ingresosHoy' => (float)$ingresosHoy,
            ],
            'charts' => [
                'asistenciaSemanal' => $asistenciaSemanal,
                'clasesPopulares' => $clasesPopulares,
            ],
            'membershipOverview' => $membresiasStatus,
            'ocupacion' => [
                'porcentaje' => $ocupacionActual,
            ],
            'instructores' => $instructoresHoy
        ]);
    }
}
