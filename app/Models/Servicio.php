<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    use HasFactory;

    protected $table = 'servicios';
    protected $primaryKey = 'idservicio';

    protected $fillable = [
        'id_local',
        'idcategoria',
        'nombre',
        'descripcion',
        'precio',
        'duracion',
        'destacado',
        'estado',
        'mostrar_feed',
        'imagen',
    ];
    protected $casts = [
        'estado' => 'boolean',
        'destacado' => 'boolean',
        'mostrar_feed' => 'boolean',
    ];


    protected $appends = ['imagen_url'];

    public function getImagenUrlAttribute()
    {
        return $this->imagen ? asset('storage/' . $this->imagen) : asset('images/default-product.jpg');
    }

    public function getPrecioUnitarioAttribute()
    {
        return $this->precio;
    }

    public function local()
    {
        return $this->belongsTo(Local::class, 'id_local');
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'idcategoria', 'id_categoria');
    }

    public function scopeActivos($query)
    {
        return $query->where('estado', true);
    }

    public function scopeDestacados($query)
    {
        return $query->where('destacado', true);
    }
}
