<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;
    protected $table = 'ventas'; // nombre de la tabla en la base de datos

    protected $fillable = [
        'idcliente',
        'tipo_venta',
        'total_venta',
        'descuento',
        'recargo',
        'pago',
        'forma_de_pago',
        'saldo',
        'estado',
        'id_local'
    ];

    // Relación con el modelo de personas (asumo que tu modelo de persona se llama Persona)
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'idcliente');
    }
    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class, 'idventa');
    }
}
