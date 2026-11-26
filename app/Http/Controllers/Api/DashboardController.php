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
use Illuminate\Support\Facades\Cache;
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

        // Cache por 5 minutos para reducir carga
        $cacheKey = "dashboard_stats_{$localId}";

        return response()->json(Cache::remember($cacheKey, 300, function () use ($localId) {
            $today = Carbon::today();
            $now = Carbon::now();

            // 1. Métricas Clave
            // Contar socios que tienen una membresía activa (no por estado_membresia sino por membresía real)
            $totalSociosActivos = Persona::where('id_local', $localId)
                ->where('tipo_persona', 'cliente')
                ->whereHas('membresias', function ($q) {
                    $q->where('estado', 'activa')
                        ->where(function ($query) {
                            // Membresías de fecha válidas
                            $query->where(function ($q) {
                                $q->where('tipo', 'fecha')
                                    ->where('fecha_fin', '>=', Carbon::today());
                            })
                            // Membresías de créditos válidas
                            ->orWhere(function ($q) {
                                $q->where('tipo', 'creditos')
                                    ->where('creditos_restantes', '>', 0)
                                    ->where(function ($dateQ) {
                                        $dateQ->whereNull('fecha_fin')
                                            ->orWhere('fecha_fin', '>=', Carbon::today());
                                    });
                            });
                        });
                })
                ->count();

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

            // Optimizar: usar una sola query para ingresos y salidas
            $ingresosHoy = PagoGym::where('id_local', $localId)
                ->whereDate('fecha_pago', $today)
                ->sum('monto');

            $salidasHoy = \App\Models\SalidaGym::where('id_local', $localId)
                ->whereDate('fecha', $today)
                ->sum('monto');

            $balanceHoy = $ingresosHoy - $salidasHoy;

            // 2. Gráfico de Asistencia (Últimos 7 días) - Optimizado con una sola query
            $asistenciaData = AsistenciaGym::where('id_local', $localId)
                ->whereBetween('fecha_asistencia', [Carbon::today()->subDays(6), Carbon::today()])
                ->selectRaw('DATE(fecha_asistencia) as fecha, COUNT(*) as count')
                ->groupBy('fecha')
                ->pluck('count', 'fecha');

            $asistenciaSemanal = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i);
                $dateStr = $date->format('Y-m-d');
                $count = $asistenciaData[$dateStr] ?? 0;

                $asistenciaSemanal[] = [
                    'day' => $date->isoFormat('ddd'),
                    'visits' => $count,
                    'percentage' => 0
                ];
            }

            // Calcular porcentaje basado en el máximo
            $maxVisits = collect($asistenciaSemanal)->max('visits') ?: 1;
            foreach ($asistenciaSemanal as &$item) {
                $item['percentage'] = round(($item['visits'] / $maxVisits) * 100);
            }

            // 3. Clases Populares (Top 5)
            $clasesPopulares = ClaseGym::where('id_local', $localId)
                ->withCount('reservas')
                ->orderBy('reservas_count', 'desc')
                ->take(5)
                ->get()
                ->map(function ($clase) {
                    return [
                        'name' => $clase->nombre,
                        'reservations' => $clase->reservas_count,
                        'trend' => '+0%'
                    ];
                });

            // 4. Estados de Membresía (Calculado por fechas)
            $porVencerStart = $today->copy();
            $porVencerEnd = $today->copy()->addDays(7);

            // Consultar tabla membresias directa (tiene id_local según modelo)
            $membresiasQuery = Membresia::where('id_local', $localId);

            $porVencer = (clone $membresiasQuery)
                ->whereBetween('fecha_fin', [$porVencerStart, $porVencerEnd])
                ->count();

            // Vencidas (recientes - últimos 30 días para ser relevante)
            $vencidas = (clone $membresiasQuery)
                ->where('fecha_fin', '<', $today)
                ->where('fecha_fin', '>=', $today->copy()->subDays(30))
                ->count();

            // Activas (más de 7 días restantes)
            $activas = (clone $membresiasQuery)
                ->where('fecha_fin', '>', $porVencerEnd)
                ->count();

            $membresiasStatus = [
                'activo' => $activas,
                'por_vencer' => $porVencer,
                'vencido' => $vencidas
            ];

            // 5. Ocupación Actual
            $currentClasses = ClaseGym::where('id_local', $localId)
                ->where('estado', 'activa')
                ->where('dias_semana', 'like', "%{$todayName}%")
                ->get();

            $totalCupos = $currentClasses->sum('cupo_maximo') ?: 1;
            $reservasAhora = ReservaGym::whereIn('id_clase_gym', $currentClasses->pluck('id'))
                ->whereDate('fecha_reserva', $today)
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

            return [
                'metrics' => [
                    'sociosActivos' => $totalSociosActivos,
                    'clasesHoy' => $clasesHoyCount,
                    'reservasSemana' => $reservasSemana,
                    'ingresosHoy' => (float)$ingresosHoy,
                    'salidasHoy' => (float)$salidasHoy,
                    'balanceHoy' => (float)$balanceHoy,
                ],
                'charts' => [
                    'asistenciaSemanal' => $asistenciaSemanal,
                    'clasesPopulares' => $clasesPopulares,
                ],
                'membershipOverview' => [
                    'activas' => $membresiasStatus['activo'] ?? 0,
                    'por_vencer' => $membresiasStatus['por_vencer'] ?? 0,
                    'vencidas' => $membresiasStatus['vencido'] ?? 0,
                ],
                'ocupacion' => [
                    'porcentaje' => $ocupacionActual,
                ],
                'instructores' => $instructoresHoy
            ];
        }));
    }

    /**
     * GET /api/dashboard/constancia-mensual?mes=YYYY-MM
     * Listado de socios con sus turnos reservados (no cancelados) en el mes,
     * sin requerir asistencia marcada por el profesor.
     */
    public function getConstanciaMensual(Request $request)
    {
        $localId = $this->getLocalId();
        if (!$localId) {
            return response()->json(['message' => 'Sin local activo'], 403);
        }

        $mesParam = $request->query('mes');
        try {
            $inicioMes = ($mesParam && preg_match('/^\d{4}-\d{2}$/', $mesParam))
                ? Carbon::createFromFormat('Y-m', $mesParam)->startOfMonth()
                : Carbon::now()->startOfMonth();
        } catch (\Exception $e) {
            $inicioMes = Carbon::now()->startOfMonth();
        }
        $finMes = $inicioMes->copy()->endOfMonth();

        $reservas = ReservaGym::with(['persona:idpersona,nombre,telefono,foto,tipo_persona'])
            ->where(function ($q) use ($localId) {
                $q->where('id_local', $localId)
                  ->orWhereHas('clase', fn($cq) => $cq->where('id_local', $localId));
            })
            ->whereBetween('fecha_reserva', [$inicioMes->toDateString(), $finMes->toDateString()])
            ->where('estado', '!=', 'cancelada')
            ->get();

        $porSocio = $reservas
            ->filter(fn($r) => $r->persona && $r->persona->tipo_persona === 'cliente')
            ->groupBy('id_persona')
            ->map(function ($items) {
                $persona = $items->first()->persona;

                $fechasUnicas = $items
                    ->map(fn($r) => Carbon::parse($r->fecha_reserva)->format('Y-m-d'))
                    ->unique()
                    ->sort()
                    ->values();

                // Calcular mejor racha de días hábiles seguidos dentro del mes (saltando sábados y domingos)
                $mejorRacha = 0;
                $rachaActual = 0;
                $prevDate = null;

                foreach ($fechasUnicas as $fechaStr) {
                    $cur = Carbon::parse($fechaStr);
                    if ($prevDate === null) {
                        $rachaActual = 1;
                    } else {
                        // Avanzar desde el día siguiente al anterior saltando fines de semana
                        $expected = $prevDate->copy()->addDay();
                        while ($expected->isWeekend() && $expected->lt($cur)) {
                            $expected->addDay();
                        }
                        if ($expected->isSameDay($cur)) {
                            $rachaActual++;
                        } else {
                            $rachaActual = 1;
                        }
                    }
                    if ($rachaActual > $mejorRacha) {
                        $mejorRacha = $rachaActual;
                    }
                    $prevDate = $cur;
                }

                $foto = $persona->foto
                    ? (preg_match('/^http/', $persona->foto) ? $persona->foto : url($persona->foto))
                    : null;

                return [
                    'id' => $persona->idpersona,
                    'nombre' => $persona->nombre,
                    'telefono' => $persona->telefono,
                    'foto' => $foto,
                    'total_turnos' => $items->count(),
                    'dias_unicos' => $fechasUnicas->count(),
                    'mejor_racha' => $mejorRacha,
                    'ultima_reserva' => $fechasUnicas->last(),
                ];
            })
            ->sort(function ($a, $b) {
                if ($b['total_turnos'] !== $a['total_turnos']) {
                    return $b['total_turnos'] <=> $a['total_turnos'];
                }
                if ($b['mejor_racha'] !== $a['mejor_racha']) {
                    return $b['mejor_racha'] <=> $a['mejor_racha'];
                }
                return strcmp($a['nombre'], $b['nombre']);
            })
            ->values();

        return response()->json([
            'mes' => $inicioMes->format('Y-m'),
            'resumen' => [
                'total_socios_con_turno' => $porSocio->count(),
                'total_turnos_mes' => $porSocio->sum('total_turnos'),
            ],
            'socios' => $porSocio,
        ]);
    }
}
