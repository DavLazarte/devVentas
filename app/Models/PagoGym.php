<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagoGym extends Model
{
    use HasFactory;

    protected $table = 'pagos_gym';

    protected $fillable = [
        'idpersona',
        'id_membresia',
        'id_local',
        'id_user',
        'monto',
        'metodo_pago',
        'fecha_pago',
        'observaciones',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha_pago' => 'date',
    ];

    // Relaciones
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'idpersona', 'idpersona');
    }

    public function membresia()
    {
        return $this->belongsTo(Membresia::class, 'id_membresia');
    }

    public function local()
    {
        return $this->belongsTo(Local::class, 'id_local');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}