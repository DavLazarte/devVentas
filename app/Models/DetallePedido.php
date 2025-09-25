<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetallePedido extends Model
{
    use HasFactory;
    protected $table = 'detalle_pedidos';

    protected $fillable = [
        'pedido_id',
        'idarticulo',
        'id_variante',
        'idservicio',
        'cantidad',
        'precio_unitario',
        'subtotal',
        'variante',
        'sku_vendido',
        'descripcion_variante',
        'id_empleado'
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2'
    ];

    public function empleado()
    {
        return $this->belongsTo(Persona::class, 'id_empleado', 'idpersona');
    }

    public function getProductoAttribute()
    {
        if ($this->idarticulo) {
            return Articulo::find($this->idarticulo);
        }

        if ($this->idservicio) {
            return Servicio::find($this->idservicio);
        }

        return null;
    }


    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function producto()
    {
        return $this->belongsTo(Articulo::class, 'idarticulo', 'idarticulo');
    }
    public function variantesArticulos()
    {
        return $this->belongsTo(ArticuloVariante::class, 'id_variante', 'id_variante');
    }
    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'idservicio', 'idservicio');
    }
}
