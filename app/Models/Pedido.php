<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_local',
        'id_user',
        'nombre_cliente',
        'email',
        'telefono',
        'direccion',
        'ciudad',
        'codigo_postal',
        'notas_entrega',
        'subtotal',
        'envio',
        'descuento',
        'total',
        'estado',
        'metodo_pago',
        'crear_cuenta',
        'fecha_servicio',        // date
        'hora_inicio',          // time
        'hora_fin',             // time (calculado automáticamente)
        'tipo_pedido',          // 'producto' o 'servicio'
        'estado_reserva',       // 'pendiente', 'confirmada', 'cancelada', 'completada'
        'fecha_confirmacion',   // timestamp
        'cancelado_por',        // 'cliente' o 'local'
        'motivo_cancelacion'    // text
    ];

    protected $casts = [
        'crear_cuenta' => 'boolean',
        'subtotal' => 'decimal:2',
        'envio' => 'decimal:2',
        'descuento' => 'decimal:2',
        'total' => 'decimal:2'
    ];

    public function local()
    {
        return $this->belongsTo(Local::class, 'id_local');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function detalles()
    {
        return $this->hasMany(DetallePedido::class, 'pedido_id');
    }
}
