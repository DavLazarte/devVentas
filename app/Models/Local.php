<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Models\User as VoyagerUser;

class Local extends Model
{
    use HasFactory;
    protected $table = 'locales';

    protected $fillable = [
    'id_user',
    'nombre',
    'slug', // Para URLs amigables (ej: 'tienda-dux')
    'direccion',
    'telefono',
    'email',
    'estado', // Ej: 'activo', 'inactivo', 'pendiente'
    'mostrar_feed',
    'tipo', // 'servicio' o 'venta' (para filtrar)
    'plan', // 'free', 'medium', 'premium'
    'descripcion', // Texto largo sobre el local
    'horario', // JSON: {"lunes": "9:00-18:00", ...}
    'latitud', // Para mapas
    'longitud',
    'foto_portada', // Imagen destacada (ej: 'locales/portada.jpg')
    'foto_logo', // Logo (ej: 'locales/logo.jpg')
    'rating_promedio', // Decimal: 4.8 (calculado automáticamente)
    'destacado', // boolean: aparece en sección "Recomendados"
    'sitio_web', // URL
    'redes_sociales', // JSON: {"facebook": "url", "instagram": "url"}
];

    public function user()
    {
        return $this->belongsTo(VoyagerUser::class, 'id_user');
    }

    public function categories() {
        return $this->belongsToMany(Category::class, 'category_local');
    }

    public function subcategories() {
        return $this->belongsToMany(Subcategory::class, 'local_subcategory');
    }
}
