<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MateriaPrimaModel extends Model
{
    use HasFactory;
    protected $table = 'materia_prima';
    protected $fillable = [
        'producto',
        'precio',
        'peso',
        'stock',
        // Otros campos si es necesario
    ];
}
