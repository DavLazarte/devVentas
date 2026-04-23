<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanCredito extends Model
{
    use HasFactory;

    protected $table = 'plan_creditos';

    protected $fillable = [
        'id_local',
        'nombre',
        'periodicidad',
        'cantidad_cuotas',
        'tasa_interes',
        'mora_diaria',
        'dias_gracia',
        'estado'
    ];

    protected $casts = [
        'estado' => 'boolean',
        'tasa_interes' => 'decimal:2',
        'mora_diaria' => 'decimal:2',
    ];

    public function local()
    {
        return $this->belongsTo(Local::class, 'id_local');
    }

    public function creditos()
    {
        return $this->hasMany(Credito::class, 'id_plan_credito');
    }
}
