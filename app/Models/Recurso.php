<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Recurso extends Model
{
    use HasFactory;

    protected $table = 'recursos';

    protected $fillable = [
        'id_local',
        'id_empleado',  // Vínculo con Persona (tipo empleado)
        'nombre',       // "Cancha 1", "Sillón Gonzalo", "Box Manicuría A"
        'tipo',         // 'cancha', 'sillon', 'cabina', 'box', 'sala', 'consultorio'
        'descripcion',
        'capacidad',    // cuántas personas simultáneas (default 1)
        'activo',
        'imagen',
        'token_staff',  // token único para el panel del empleado (sin login)
    ];

    protected $casts = [
        'activo'    => 'boolean',
        'capacidad' => 'integer',
    ];

    // Auto-generar token_staff al crear un recurso nuevo
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($recurso) {
            if (empty($recurso->token_staff)) {
                $recurso->token_staff = Str::random(32);
            }
        });
    }


    // ── Relaciones ────────────────────────────────────────────────

    public function local()
    {
        return $this->belongsTo(Local::class, 'id_local');
    }

    public function empleado()
    {
        return $this->belongsTo(Persona::class, 'id_empleado', 'idpersona');
    }

    // Servicios que se pueden realizar en/con este recurso
    public function servicios()
    {
        return $this->belongsToMany(Servicio::class, 'servicio_recurso', 'recurso_id', 'servicio_id');
    }

    // Horarios de disponibilidad propios del recurso
    public function horarios()
    {
        return $this->hasMany(HorarioDisponibilidad::class, 'recurso_id');
    }

    // Bloqueos puntuales (mantenimiento, no disponible)
    public function bloqueos()
    {
        return $this->hasMany(BloqueoHorario::class, 'recurso_id');
    }

    // Detalle de pedidos/turnos que usan este recurso
    public function detallesPedidos()
    {
        return $this->hasMany(DetallePedido::class, 'recurso_id');
    }

    // ── Scopes ───────────────────────────────────────────────────

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeDelLocal($query, $idLocal)
    {
        return $query->where('id_local', $idLocal);
    }
}
