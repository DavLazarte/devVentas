<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anuncio extends Model
{
    use HasFactory;

    protected $table = 'anuncios';

    protected $fillable = [
        'local_id',
        'titulo',
        'descripcion',
        'tipo',
        'estado',
    ];

    public function local()
    {
        return $this->belongsTo(Local::class, 'local_id');
    }
}
