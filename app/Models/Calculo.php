<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calculo extends Model
{
    use HasFactory;
    protected $fillable = ['id_insumos', 'empleado','costo_elaboracion', 'costo_unitario', 'porcentaje_ganancia', 'precio', 'iva', 'precio_iva', 'ganancia'];

    public function insumo()
    {
        return $this->hasOne(Insumo::class, 'id_calculos');
    }
}
