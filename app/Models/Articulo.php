<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Articulo extends Model
{
    use HasFactory;

    protected $table = 'articulos';
    protected $primaryKey = 'idarticulo';
    protected $fillable = ['idcategoria', 'codigo', 'nombre', 'stock', 'descripcion', 'imagen', 'precio_unitario', 'estado', 'mostrar_feed', 'destacado', 'id_local'];

    protected $appends = ['imagen_url'];

    public function getImagenUrlAttribute()
    {
        return $this->imagen ? asset('storage/' . $this->imagen) : asset('images/default-product.jpg');
    }

    // Relación con la tabla Categoria
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'idcategoria', 'id_categoria');
    }
    public function local()
    {
        return $this->belongsTo(Local::class, 'id_local');
    }

    public function scopeDestacados($query)
    {
        return $query->where('destacado', true);
    }
}
