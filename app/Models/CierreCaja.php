<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CierreCaja extends Model
{
    use HasFactory;

    protected $table = 'cierre_cajas';
    // protected $primaryKey = 'id';

    protected $fillable = [
        'fecha_apertura',
        'fecha_cierre',
        'monto_total_ventas',
        'monto_apertura',
        'monto_cierre',
        'monto_cierre_real',
        'ventas_efectivo',
        'ventas_transferencia',
        'ventas_tarjeta',
        'ingresos',
        'salidas',
        'estado',
    ];
}
