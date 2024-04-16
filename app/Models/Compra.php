<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    use HasFactory;

    protected $fillable = [
        'idpersona',
        'tipo_pago',
        'pago',
        'tipo_compra',
        'num_recibo',
        'total',
        'saldo',
        'id_local'
    ];

    public function detallesCompra()
    {
        return $this->hasMany(Detalle_compra::class, 'id_compra');
    }

    public function proveedor()
    {
        return $this->belongsTo(Persona::class, 'idpersona');
    }
}
