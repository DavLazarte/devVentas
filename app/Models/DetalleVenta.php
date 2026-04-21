<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleVenta extends Model
{
    use HasFactory;
    protected $table = 'detalle_ventas'; // nombre de la tabla en la base de datos

    protected $fillable = [
        'idventa',
        'idarticulo',
        'cantidad',
        'precio_venta',
        'estado',
        'id_variante',
        'sku_vendido',
        'descripcion_variante',
        // Campos para venta por peso/volumen
        'cantidad_decimal',
        'unidad_medida_venta',
    ];

    protected $casts = [
        'cantidad_decimal' => 'decimal:3'
    ];

    // Relación con el modelo de venta
    public function venta()
    {
        return $this->belongsTo(Venta::class, 'idventa');
    }

    public function productos()
    {
        return $this->belongsToMany(Articulo::class, 'detalle_ventas', 'idventa', 'idarticulo')
            ->withPivot(['cantidad', 'precio_venta']); // Asegúrate de incluir los campos pivot necesarios
    }

    public function producto()
    {
        return $this->belongsTo(Articulo::class, 'idarticulo');
    }

    public function variantesArticulos()
    {
        return $this->belongsTo(ArticuloVariante::class, 'id_variante', 'id_variante');
    }

    public function getNombreProductoAttribute()
    {
        if ($this->variantesArticulos) {
            return ($this->producto->nombre ?? 'Producto eliminado') . ' - ' . $this->variantesArticulos->descripcion_variante;
        }
        return $this->producto->nombre ?? 'Producto eliminado';
    }
}
