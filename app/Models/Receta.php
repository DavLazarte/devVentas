<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receta extends Model
{
    use HasFactory;
    protected $fillable = ['nombre', 'descripcion', 'porciones', 'id_insumo', 'id_calculos'];

    public function insumos()
    {
        return $this->hasMany(Insumo::class, 'id_receta');
    }
    public function calculo()
    {
        return $this->belongsTo(Calculo::class, 'id_calculos');
    }
}
