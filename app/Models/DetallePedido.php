<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetallePedido extends Model
{
    use HasFactory;

    protected $fillable = [
        'pedido_id',
        'idarticulo',
        'idservicio',
        'cantidad',
        'precio_unitario',
        'subtotal',
        'variante'
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2'
    ];

    // app/Models/PedidoDetalle.php

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
    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'idservicio', 'idservicio');
    }
}
