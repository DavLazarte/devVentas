<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salida extends Model
{
    use HasFactory;

    protected $table = 'salidas';
    protected $primaryKey = 'idsalida';

    protected $fillable = [
        'idpersona',
        'tipo_salida',
        'monto',
        'descripcion',
        'saldo',
        'estado',
    ];

    // Relación con la tabla personas
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'idpersona');
    }
    
}
