<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanificacionGym extends Model
{
    use HasFactory;

    protected $table = 'planificaciones_gym';

    protected $fillable = [
        'id_local',
        'fecha',
        'titulo',
        'contenido',
        'created_by',
    ];

    protected $casts = [
        'fecha' => 'date:Y-m-d',
    ];

    public function local()
    {
        return $this->belongsTo(Local::class, 'id_local');
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
