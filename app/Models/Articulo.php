<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Articulo extends Model
{
    use HasFactory;

    protected $table = 'articulos';
    protected $primaryKey = 'idarticulo';
    protected $fillable = ['idcategoria', 'codigo', 'nombre', 'stock', 'descripcion', 'precio_unitario', 'estado','id_local'];

    // Relación con la tabla Categoria
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'idcategoria', 'id_categoria');
    }
}
