<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cuota extends Model
{
    use HasFactory;

    protected $table = 'cuotas';

    protected $fillable = [
        'id_credito',
        'nro_cuota',
        'monto',
        'monto_mora',
        'fecha_vencimiento',
        'fecha_pago',
        'estado'
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
        'fecha_pago' => 'datetime',
        'monto' => 'decimal:2',
        'monto_mora' => 'decimal:2',
    ];

    public function credito()
    {
        return $this->belongsTo(Credito::class, 'id_credito');
    }

    public function pagos()
    {
        return $this->belongsToMany(PagoCuota::class, 'pago_cuota_cuota', 'id_cuota', 'id_pago_cuota')
            ->withPivot('monto_aplicado')
            ->withTimestamps();
    }
}
