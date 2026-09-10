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
        'tipo_reserva', // 'sin_reserva', 'coordinacion', 'turno_fijo', 'cola_virtual'

        // Campos específicos para gimnasio
        'tipo_servicio',      // 'plan', 'clase', 'servicio_general'
        'duracion_dias',      // Para planes (30, 90, 365 días)
        'creditos',           // Para planes por créditos
        'cupo_maximo',        // Para clases grupales
    ];
    protected $casts = [
        'estado' => 'boolean',
        'destacado' => 'boolean',
        'mostrar_feed' => 'boolean',
        'es_reservable' => 'boolean',
        'duracion_dias' => 'integer',
        'creditos' => 'integer',
        'cupo_maximo' => 'integer',
        'precio' => 'decimal:2',
    ];


    protected $appends = ['imagen_url'];

    public function getImagenUrlAttribute()
    {
        return $this->imagen ? asset('storage/' . $this->imagen) : null;
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
     // NUEVAS RELACIONES PARA GIMNASIO
    
    // Membresías que usan este servicio como plan
    public function membresias()
    {
        return $this->hasMany(Membresia::class, 'idservicio', 'idservicio');
    }

    // Clases que son de este tipo
    public function clases()
    {
        return $this->hasMany(ClaseGym::class, 'idservicio', 'idservicio');
    }


    public function empleados()
    {
        return $this->belongsToMany(Persona::class, 'servicio_empleado', 'servicio_id', 'empleado_id');
    }

    // Recursos físicos asociados a este servicio (canchas, sillones, boxes, etc.)
    public function recursos()
    {
        return $this->belongsToMany(Recurso::class, 'servicio_recurso', 'servicio_id', 'recurso_id');
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
     // Solo planes de membresía
    public function scopePlanes($query)
    {
        return $query->where('tipo_servicio', 'plan');
    }

    // Solo tipos de clases
    public function scopeTiposClase($query)
    {
        return $query->where('tipo_servicio', 'clase');
    }

    // Planes por fecha (mensuales, anuales, etc)
    public function scopePlanesFecha($query)
    {
        return $query->planes()->whereNotNull('duracion_dias');
    }

    // Planes por créditos
    public function scopePlanesCreditos($query)
    {
        return $query->planes()->whereNotNull('creditos');
    }

    // ACCESSORS/HELPERS PARA GIMNASIO
    
    // Verificar si es un plan
    public function getEsPlanAttribute()
    {
        return $this->tipo_servicio === 'plan';
    }

    // Verificar si es una clase
    public function getEsClaseAttribute()
    {
        return $this->tipo_servicio === 'clase';
    }

    // Obtener tipo de plan (fecha o créditos)
    public function getTipoPlanAttribute()
    {
        if (!$this->es_plan) {
            return null;
        }

        if ($this->duracion_dias) {
            return 'fecha';
        }

        if ($this->creditos) {
            return 'creditos';
        }

        return null;
    }

    // Descripción del plan para mostrar
    public function getDescripcionPlanAttribute()
    {
        if (!$this->es_plan) {
            return null;
        }

        if ($this->duracion_dias) {
            $meses = round($this->duracion_dias / 30);
            if ($meses == 1) return "Plan Mensual";
            if ($meses == 3) return "Plan Trimestral";
            if ($meses == 6) return "Plan Semestral";
            if ($meses == 12) return "Plan Anual";
            return "{$this->duracion_dias} días";
        }

        if ($this->creditos) {
            return "{$this->creditos} créditos";
        }

        return null;
    }

}
