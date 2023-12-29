<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Insumo extends Model
{
    use HasFactory;
    protected $fillable = ['id_receta', 'id_materia', 'cantidad', 'unidad', 'costo_en_receta'];

    public function receta()
    {
        return $this->belongsTo(Receta::class, 'id_receta');
    }

    public function materiasPrimas()
    {
        return $this->belongsToMany(MateriaPrimaModel::class, 'insumo_materia_prima', 'insumo_id', 'materia_prima_id')->withPivot('cantidad', 'costo_en_receta');
    }
}
