<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    use HasFactory;

    protected $table = 'personas';
    protected $primaryKey = 'idpersona';


    protected $fillable = [
        'tipo_persona',
        'nombre',
        'direccion',
        'telefono',
        'mail',
        'estado',
        'id_local'
    ];
    public function ventas()
    {
        return $this->hasMany(Venta::class, 'idcliente');
    }
    public function compras()
    {
        return $this->hasMany(Compra::class, 'idpersona');
    }
}
