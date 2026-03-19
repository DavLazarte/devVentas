<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Membresia extends Model
{
    use HasFactory;

    protected $table = 'membresias';

    protected $fillable = [
        'idpersona',
        'idservicio',
        'id_local',
        'tipo',
        'fecha_inicio',
        'fecha_fin',
        'creditos_totales',
        'creditos_restantes',
        'monto_total',
        'estado',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'creditos_totales' => 'integer',
        'creditos_restantes' => 'integer',
        'monto_total' => 'float',
    ];

    // Relaciones
    public function socio()
    {
        return $this->belongsTo(Persona::class, 'idpersona', 'idpersona');
    }

    public function plan()
    {
        return $this->belongsTo(Servicio::class, 'idservicio', 'idservicio');
    }

    public function local()
    {
        return $this->belongsTo(Local::class, 'id_local');
    }

    public function pagos()
    {
        return $this->hasMany(PagoGym::class, 'id_membresia');
    }

    public function reservas()
    {
        return $this->hasMany(ReservaGym::class, 'id_membresia');
    }

    public function asistencias()
    {
        return $this->hasMany(AsistenciaGym::class, 'id_membresia');
    }

    // Accesor para verificar si está activa
    public function getEsActivaAttribute()
    {
        if ($this->estado !== 'activa') {
            return false;
        }

        if ($this->tipo === 'fecha') {
            return $this->fecha_fin >= Carbon::now();
        }

        if ($this->tipo === 'creditos') {
            return $this->creditos_restantes > 0;
        }

        return false;
    }

    // Accesor para saldo pendiente
    public function getSaldoPendienteAttribute()
    {
        $pagado = $this->pagos()->sum('monto');
        $saldo = $this->monto_total - $pagado;
        return $saldo > 0 ? $saldo : 0;
    }

    public function getTotalPagadoAttribute()
    {
        return $this->pagos()->sum('monto');
    }

    public function getDiasRestantesAttribute()
    {
        if ($this->tipo !== 'fecha' || !$this->fecha_fin) {
            return null;
        }

        $dias = Carbon::now()->diffInDays($this->fecha_fin, false);
        return $dias > 0 ? $dias : 0;
    }

    // Estado calculado en tiempo real
    public function getComputedStatusAttribute()
    {
        // Respetar estados manuales explícitos — si fue marcada como vencida/cancelada/renovada en BD, prevalece
        if (in_array($this->estado, ['vencida', 'cancelada', 'renovada'])) {
            return $this->estado;
        }

        if ($this->tipo === 'fecha') {
            $today = Carbon::today();
            $fechaFin = $this->fecha_fin;

            if (!$fechaFin) return 'activa'; // Indefinido?

            if ($fechaFin < $today) {
                return 'vencida';
            }

            if ($fechaFin <= $today->copy()->addDays(7)) {
                return 'por_vencer';
            }

            return 'activa';
        }

        // Para créditos
        if ($this->tipo === 'creditos') {
            if ($this->creditos_restantes <= 0) return 'vencida';
            // Si tiene fecha fin además de créditos
            if ($this->fecha_fin && $this->fecha_fin < Carbon::today()) return 'vencida';

            return 'activa';
        }

        return $this->estado; // Fallback
    }
}
