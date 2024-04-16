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
}
