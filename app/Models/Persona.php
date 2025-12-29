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
        'dni_cuit',
        'direccion',
        'telefono',
        'mail',
        'estado',
        'id_local',
        'user_id',
        'fecha_nacimiento',
        'foto',
        'estado_membresia',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
    ];

    // Relación con User (NUEVA)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function ventas()
    {
        return $this->hasMany(Venta::class, 'idcliente');
    }
    public function compras()
    {
        return $this->hasMany(Compra::class, 'idpersona');
    }

    public function membresias()
    {
        return $this->hasMany(Membresia::class, 'idpersona');
    }

    public function servicios()
    {
        return $this->belongsToMany(Servicio::class, 'servicio_empleado', 'empleado_id', 'servicio_id');
    }
    // Accesor para obtener membresía activa
    public function getMembresiaActivaAttribute()
    {
        return $this->membresias()
            ->where('estado', 'activa')
            ->where('fecha_fin', '>=', now())
            ->first();
    }

    // Accesor para estado de membresía
    public function getEstadoMembresiaCalculadoAttribute()
    {
        $membresia = $this->membresia_activa;

        if (!$membresia) {
            return 'inactivo';
        }

        // 1. Verificar vencimiento por FECHA (siempre que tenga fecha_fin)
        if ($membresia->fecha_fin) {
            $diasRestantes = now()->diffInDays($membresia->fecha_fin, false);
            if ($diasRestantes < 0) {
                return 'vencido';
            }
        }

        // 2. Verificar vencimiento por CRÉDITOS (si es tipo créditos)
        if ($membresia->tipo === 'creditos') {
            if ($membresia->creditos_restantes <= 0) {
                return 'vencido';
            }
        }

        return 'activo';
    }

    /**
     * Sincroniza el campo estado_membresia de la tabla personas
     * con la membresía activa actual.
     */
    public function syncEstadoMembresia()
    {
        $this->update([
            'estado_membresia' => $this->estado_membresia_calculado
        ]);
    }
}
