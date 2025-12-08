<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Detalle_compra extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_compra',
        'idarticulo',
        'cantidad',
        'precio_compra',
        'estado',
        'id_variante',
        'sku_comprado',
        'descripcion_variante',
        'cantidad_decimal',
        'unidad_medida_compra',
    ];

    protected $casts = [
        'cantidad_decimal' => 'decimal:3',
    ];

    public function compra()
    {
        return $this->belongsTo(Compra::class, 'id_compra');
    }

    public function articulo()
    {
        return $this->belongsTo(Articulo::class, 'idarticulo');
    }

    public function variante()
    {
        return $this->belongsTo(ArticuloVariante::class, 'id_variante', 'id_variante');
    }

    public function getNombreProductoAttribute()
    {
        if ($this->variante) {
            return $this->articulo->nombre . ' - ' . $this->variante->descripcion_variante;
        }
        return $this->articulo->nombre ?? 'Producto eliminado';
    }
}
