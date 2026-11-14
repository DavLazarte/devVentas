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
        'imagen',
        'tipo',
        'estado',
        'en_slider',
    ];

    protected $casts = [
        'en_slider' => 'boolean',
    ];

    protected $appends = ['imagen_url'];

    public function getImagenUrlAttribute()
    {
        if (!$this->imagen) return null;
        if (filter_var($this->imagen, FILTER_VALIDATE_URL)) return $this->imagen;

        $path = str_replace('\\', '/', $this->imagen);
        return asset('storage/' . $path);
    }

    public function local()
    {
        return $this->belongsTo(Local::class, 'local_id');
    }
}
