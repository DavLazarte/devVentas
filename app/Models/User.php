<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends \TCG\Voyager\Models\User
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Relación con la tabla roles
    public function role()
    {
        return $this->belongsTo(Role::class);
    }
    // NUEVA: Relación con Persona
    public function persona()
    {
        return $this->hasOne(Persona::class, 'user_id');
    }
     // Relación con User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relaciones de gimnasio
    public function membresias()
    {
        return $this->hasMany(Membresia::class, 'idpersona', 'idpersona');
    }
     public function pagos()
    {
        return $this->hasMany(PagoGym::class, 'idpersona', 'idpersona');
    }

    public function reservas()
    {
        return $this->hasMany(ReservaGym::class, 'id_persona', 'idpersona');
    }

    public function asistencias()
    {
        return $this->hasMany(AsistenciaGym::class, 'id_persona', 'idpersona');
    }

    public function clasesComoCoach()
    {
        return $this->hasMany(ClaseGym::class, 'id_coach', 'idpersona');
    }

    // Relaciones existentes
    public function ventas()
    {
        return $this->hasMany(Venta::class, 'idcliente');
    }

    public function compras()
    {
        return $this->hasMany(Compra::class, 'idpersona');
    }

    public function servicios()
    {
        return $this->belongsToMany(Servicio::class, 'servicio_empleado', 'empleado_id', 'servicio_id');
    }

    // Accesor para membresía activa
    public function getMembresiaActivaAttribute()
    {
        return $this->membresias()
            ->where('estado', 'activa')
            ->where(function($query) {
                $query->where('tipo', 'creditos')
                      ->where('creditos_restantes', '>', 0)
                      ->orWhere(function($q) {
                          $q->where('tipo', 'fecha')
                            ->where('fecha_fin', '>=', now());
                      });
            })
            ->first();
    }

    // Accesor para estado de membresía
    public function getEstadoMembresiaAttribute()
    {
        $membresia = $this->membresia_activa;
        
        if (!$membresia) {
            return 'inactivo';
        }

        if ($membresia->tipo === 'fecha') {
            $diasRestantes = now()->diffInDays($membresia->fecha_fin, false);
            
            if ($diasRestantes < 0) {
                return 'vencido';
            }
            
            if ($diasRestantes <= 7) {
                return 'por_vencer';
            }
        }

        if ($membresia->tipo === 'creditos' && $membresia->creditos_restantes <= 2) {
            return 'por_agotar';
        }
        
        return 'activo';
    }
}
