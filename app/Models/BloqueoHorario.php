<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BloqueoHorario extends Model
{
    use HasFactory;

    protected $table = 'bloqueos_horario';

    protected $fillable = [
        'id_local',
        'idservicio',
        'recurso_id',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'motivo',
    ];

    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'idservicio');
    }

    public function recurso()
    {
        return $this->belongsTo(Recurso::class, 'recurso_id');
    }
}
