<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingreso extends Model
{
    use HasFactory;
    protected $table = 'ingresos';
    protected $primaryKey = 'id_ingreso';
    protected $fillable = ['idpersona', 'monto', 'tipo_pago', 'tipo_ingreso', 'origen', 'descripcion', 'saldo', 'estado', 'id_local', 'created_at', 'updated_at'];

    public function cliente()
    {
        return $this->belongsTo(Persona::class, 'idpersona');
    }
}
