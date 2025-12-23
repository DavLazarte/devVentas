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
        'estado',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'creditos_totales' => 'integer',
        'creditos_restantes' => 'integer',
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

    // Accesor para días restantes (solo para tipo fecha)
    public function getDiasRestantesAttribute()
    {
        if ($this->tipo !== 'fecha' || !$this->fecha_fin) {
            return null;
        }

        $dias = Carbon::now()->diffInDays($this->fecha_fin, false);
        return $dias > 0 ? $dias : 0;
    }
}