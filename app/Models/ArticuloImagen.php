<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticuloImagen extends Model
{
    use HasFactory;

    protected $table = 'articulo_imagenes';

    protected $fillable = [
        'idarticulo',
        'variante_id',
        'ruta',
        'orden',
    ];

    protected $appends = ['url'];

    // ── Relaciones ─────────────────────────────────────────────

    public function articulo()
    {
        return $this->belongsTo(Articulo::class, 'idarticulo', 'idarticulo');
    }

    public function variante()
    {
        return $this->belongsTo(ArticuloVariante::class, 'variante_id', 'id_variante');
    }

    // ── Accessors ───────────────────────────────────────────────

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->ruta);
    }
}
