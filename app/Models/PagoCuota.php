<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagoCuota extends Model
{
    use HasFactory;

    protected $table = 'pago_cuotas';

    protected $fillable = [
        'id_credito',
        'idpersona',
        'id_cobrador',
        'monto_pagado',
        'fecha_pago',
        'metodo_pago',
        'observaciones'
    ];

    protected $casts = [
        'fecha_pago' => 'datetime',
        'monto_pagado' => 'decimal:2',
    ];

    public function credito()
    {
        return $this->belongsTo(Credito::class, 'id_credito');
    }

    public function cliente()
    {
        return $this->belongsTo(Persona::class, 'idpersona', 'idpersona');
    }

    public function cobrador()
    {
        return $this->belongsTo(User::class, 'id_cobrador');
    }

    public function cuotas()
    {
        return $this->belongsToMany(Cuota::class, 'pago_cuota_cuota', 'id_pago_cuota', 'id_cuota')
                    ->withPivot('monto_aplicado')
                    ->withTimestamps();
    }
}
