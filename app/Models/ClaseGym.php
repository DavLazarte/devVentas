<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClaseGym extends Model
{
    use HasFactory;

    protected $table = 'clases_gym';

    protected $fillable = [
        'nombre',
        'idservicio',
        'id_local',
        'id_coach',
        'dias_semana',
        'hora_inicio',
        'hora_fin',
        'duracion_minutos',
        'cupo_maximo',
        'estado',
        'minutos_limite_reserva',
        'minutos_limite_cancelacion',
    ];

    protected $casts = [
        'dias_semana' => 'string', // Store as comma separated for now
        'hora_inicio' => 'datetime:H:i',
        'hora_fin' => 'datetime:H:i',
        'duracion_minutos' => 'integer',
        'cupo_maximo' => 'integer',
        'minutos_limite_reserva' => 'integer',
        'minutos_limite_cancelacion' => 'integer',
    ];

    // Relaciones
    public function tipoClase()
    {
        return $this->belongsTo(Servicio::class, 'idservicio', 'idservicio');
    }

    public function local()
    {
        return $this->belongsTo(Local::class, 'id_local');
    }

    public function coach()
    {
        return $this->belongsTo(Persona::class, 'id_coach', 'idpersona');
    }

    public function reservas()
    {
        return $this->hasMany(ReservaGym::class, 'id_clase_gym');
    }

    public function asistencias()
    {
        return $this->hasMany(AsistenciaGym::class, 'id_clase_gym');
    }

    // Accesor para cupos disponibles
    public function getCuposDisponiblesAttribute()
    {
        $reservadas = $this->reservas()->whereIn('estado', ['reservada', 'asistio'])->count();
        return $this->cupo_maximo - $reservadas;
    }

    // Accesor para verificar si está llena
    public function getEstaLlenaAttribute()
    {
        return $this->cupos_disponibles <= 0;
    }
}
