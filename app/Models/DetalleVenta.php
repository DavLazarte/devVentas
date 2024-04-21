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
}
