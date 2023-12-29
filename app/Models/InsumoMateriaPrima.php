<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsumoMateriaPrima extends Model
{
    use HasFactory;

    protected $table = 'insumo_materia_prima';
    protected $fillable = ['insumo_id','materia_prima_id','cantidad','costo_en_receta'];

}
