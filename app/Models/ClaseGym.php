<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClaseGym extends Model
{
    use HasFactory;

    protected $table = 'clases_gym';

    protected $fillable = [
        'idservicio',
        'id_local',
        'id_coach',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'cupo_maximo',
        'estado',
    ];

    protected $casts = [
        'fecha' => 'date',
        'hora_inicio' => 'datetime:H:i',
        'hora_fin' => 'datetime:H:i',
        'cupo_maximo' => 'integer',
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