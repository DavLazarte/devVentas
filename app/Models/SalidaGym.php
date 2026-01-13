<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalidaGym extends Model
{
    use HasFactory;

    protected $table = 'salidas_gym';

    protected $fillable = [
        'monto',
        'descripcion',
        'tipo_salida',
        'fecha',
        'id_persona',
        'id_usuario',
        'id_local',
    ];

    protected $casts = [
        'fecha' => 'date',
        'monto' => 'decimal:2',
    ];

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
