<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HorarioDisponibilidad extends Model
{
    use HasFactory;
    protected $table = 'horarios_disponibilidad';

    protected $fillable = [
        'id_local',
        'idservicio',
        'dia_semana',
        'hora_inicio',
        'hora_fin',
        'activo',
    ];

    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'idservicio');
    }
}
