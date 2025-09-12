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
        'es_reservable',
        'buffer_tiempo',         // tiempo entre servicios (5-15 min)
        'anticipacion_minima',   // ej: 24 horas mínimo para reservar
        'anticipacion_maxima',   // ej: 30 días máximo adelante
        'cancelacion_limite',    // horas antes para cancelar
        'tipo_reserva', // 'sin_reserva', 'coordinacion', 'turno_fijo'
    ];
    protected $casts = [
        'estado' => 'boolean',
        'destacado' => 'boolean',
        'mostrar_feed' => 'boolean',
        'es_reservable' => 'boolean',
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

    // En App/Models/Servicio.php
    public function horarios()
    {
        return $this->hasMany(HorarioDisponibilidad::class, 'idservicio', 'idservicio');
    }
    // app/Models/Servicio.php

    public function empleados()
    {
        return $this->belongsToMany(Persona::class, 'servicio_empleado', 'servicio_id', 'empleado_id');
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
