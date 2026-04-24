<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Credito extends Model
{
    use HasFactory;

    protected $table = 'creditos';

    protected $fillable = [
        'idpersona',
        'id_local',
        'id_cobrador',
        'id_plan_credito',
        'tipo',
        'monto_aprobado',
        'total_a_pagar',
        'saldo_pendiente',
        'periodicidad',
        'cantidad_cuotas',
        'tasa_aplicada',
        'monto_cuota',
        'fecha_otorgamiento',
        'fecha_primer_vencimiento',
        'estado',
        'observaciones'
    ];

    protected $casts = [
        'fecha_otorgamiento' => 'date',
        'fecha_primer_vencimiento' => 'date',
        'monto_aprobado' => 'decimal:2',
        'total_a_pagar' => 'decimal:2',
        'saldo_pendiente' => 'decimal:2',
        'tasa_aplicada' => 'decimal:2',
        'monto_cuota' => 'decimal:2',
    ];

    public function cliente()
    {
        return $this->belongsTo(Persona::class, 'idpersona', 'idpersona');
    }

    public function local()
    {
        return $this->belongsTo(Local::class, 'id_local');
    }

    public function cobrador()
    {
        return $this->belongsTo(User::class, 'id_cobrador');
    }

    public function plan()
    {
        return $this->belongsTo(PlanCredito::class, 'id_plan_credito');
    }

    public function cuotas()
    {
        return $this->hasMany(Cuota::class, 'id_credito');
    }

    public function refinanciaciones()
    {
        return $this->hasMany(Refinanciacion::class, 'id_credito_original');
    }
}
