<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    use HasFactory;

    protected $table = 'personas';
    protected $primaryKey = 'idpersona';


    protected $fillable = [
        'tipo_persona',
        'nombre',
        'dni_cuit',
        'direccion',
        'telefono',
        'mail',
        'estado',
        'id_local',
        'user_id',
        'fecha_nacimiento',
        'foto',
        'estado_membresia',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
    ];

    // Relación con User (NUEVA)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function ventas()
    {
        return $this->hasMany(Venta::class, 'idcliente');
    }
    public function compras()
    {
        return $this->hasMany(Compra::class, 'idpersona');
    }

    public function membresias()
    {
        return $this->hasMany(Membresia::class, 'idpersona');
    }

    public function servicios()
    {
        return $this->belongsToMany(Servicio::class, 'servicio_empleado', 'empleado_id', 'servicio_id');
    }

    public function clasesCoach()
    {
        return $this->hasMany(ClaseGym::class, 'id_coach', 'idpersona');
    }
    // Accesor para obtener membresía activa
    public function getMembresiaActivaAttribute()
    {
        // Si la relación ya está cargada, filtrar en memoria para evitar N+1
        if ($this->relationLoaded('membresias')) {
            return $this->membresias
                ->where('estado', 'activa')
                ->filter(function ($m) {
                    if ($m->tipo === 'fecha') {
                        return $m->fecha_fin >= now();
                    }
                    if ($m->tipo === 'creditos') {
                        $dateValid = is_null($m->fecha_fin) || $m->fecha_fin >= now();
                        return $m->creditos_restantes > 0 && $dateValid;
                    }
                    return false;
                })
                ->first();
        }

        return $this->membresias()
            ->where('estado', 'activa')
            ->where(function ($query) {
                // Membresías de fecha: verificar fecha_fin
                $query->where(function ($q) {
                    $q->where('tipo', 'fecha')
                        ->where('fecha_fin', '>=', now());
                })
                    // Membresías de créditos: verificar créditos Y (fecha_fin NULL o futura)
                    ->orWhere(function ($q) {
                        $q->where('tipo', 'creditos')
                            ->where('creditos_restantes', '>', 0)
                            ->where(function ($dateQ) {
                                $dateQ->whereNull('fecha_fin')
                                    ->orWhere('fecha_fin', '>=', now());
                            });
                    });
            })
            ->first();
    }

    // Accesor para estado de membresía
    public function getEstadoMembresiaCalculadoAttribute()
    {
        $membresia = $this->membresia_activa;

        if (!$membresia) {
            return 'inactivo';
        }

        // 1. Verificar vencimiento por FECHA (siempre que tenga fecha_fin)
        if ($membresia->fecha_fin) {
            $diasRestantes = now()->diffInDays($membresia->fecha_fin, false);
            if ($diasRestantes < 0) {
                return 'vencido';
            }
        }

        // 2. Verificar vencimiento por CRÉDITOS (si es tipo créditos)
        if ($membresia->tipo === 'creditos') {
            if ($membresia->creditos_restantes <= 0) {
                return 'vencido';
            }
        }

        return 'activo';
    }

    /**
     * Sincroniza el campo estado_membresia de la tabla personas
     * con la membresía activa actual.
     */
    public function syncEstadoMembresia()
    {
        $this->update([
            'estado_membresia' => $this->estado_membresia_calculado
        ]);
    }

    public function asistencias()
    {
        return $this->hasMany(AsistenciaGym::class, 'id_persona', 'idpersona');
    }

    /**
     * Accesor para la racha actual (días seguidos entrenando).
     * Ignora sábados y domingos (no rompen la racha).
     */
    public function getRachaActualAttribute()
    {
        // Si la relación ya está cargada, usar la colección para evitar N+1
        if ($this->relationLoaded('asistencias')) {
            $dates = $this->asistencias
                ->sortByDesc('fecha_asistencia')
                ->map(fn($a) => \Carbon\Carbon::parse($a->fecha_asistencia)->format('Y-m-d'))
                ->unique()
                ->values();
        } else {
            // Obtener fechas únicas de asistencia ordenadas desc
            $dates = $this->asistencias()
                ->orderBy('fecha_asistencia', 'desc')
                ->pluck('fecha_asistencia')
                ->map(fn($d) => $d->format('Y-m-d'))
                ->unique()
                ->values();
        }

        if ($dates->isEmpty()) return 0;

        $streak = 0;
        $checkDate = \Carbon\Carbon::today();
        $dateIdx = 0;

        // Si hoy no asistió, verificar si ayer sí, o si fue finde
        // El bucle se encarga de saltar findes.

        // Primero: ¿está la racha "viva"? 
        // Si no asistió hoy ni ayer, y ayer no fue finde, murió.
        // O más simple: buscar el primer día de asistencia hacia atrás.

        $foundStart = false;
        $tempDate = \Carbon\Carbon::today();

        // Limite de búsqueda hacia atrás para considerar racha viva: 3 días (por si es lunes y el ultimo fue viernes)
        for ($i = 0; $i < 4; $i++) {
            if ($dates->contains($tempDate->format('Y-m-d'))) {
                $foundStart = true;
                $checkDate = $tempDate->copy();
                break;
            }
            if (!$tempDate->isWeekend() && $i > 0) {
                // Si ya pasamos por un día de semana y no hay asistencia, racha rota
                // (i=0 es hoy, i=1 es ayer. Si hoy es martes y no asistió hoy ni ayer lunes, rompe)
                if ($i >= 1) break;
            }
            $tempDate->subDay();
        }

        if (!$foundStart) return 0;

        // Calcular consecutivas
        while ($dateIdx < $dates->count()) {
            $formattedCheck = $checkDate->format('Y-m-d');

            if ($dates->contains($formattedCheck)) {
                $streak++;
                // Avanzar al día anterior para seguir contando
                $checkDate->subDay();
            } elseif ($checkDate->isWeekend()) {
                // Saltar finde sin romper racha
                $checkDate->subDay();
            } else {
                // Día de semana sin asistencia: fin de racha
                break;
            }
        }

        return $streak;
    }
}
