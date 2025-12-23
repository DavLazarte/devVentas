<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsistenciaGym extends Model
{
    use HasFactory;

    protected $table = 'asistencias_gym';

    protected $fillable = [
        'id_persona',
        'id_clase_gym',
        'id_membresia',
        'id_local',
        'fecha_asistencia',
    ];

    protected $casts = [
        'fecha_asistencia' => 'datetime',
    ];

    // Relaciones
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'idpersona');
    }

    public function clase()
    {
        return $this->belongsTo(ClaseGym::class, 'id_clase_gym');
    }

    public function membresia()
    {
        return $this->belongsTo(Membresia::class, 'id_membresia');
    }

    public function local()
    {
        return $this->belongsTo(Local::class, 'id_local');
    }
}
