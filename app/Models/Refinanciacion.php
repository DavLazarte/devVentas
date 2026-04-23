<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Refinanciacion extends Model
{
    use HasFactory;

    protected $table = 'refinanciaciones';

    protected $fillable = [
        'id_credito_original',
        'id_credito_nuevo',
        'id_usuario',
        'saldo_anterior',
        'motivo',
        'fecha'
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'saldo_anterior' => 'decimal:2',
    ];

    public function creditoOriginal()
    {
        return $this->belongsTo(Credito::class, 'id_credito_original');
    }

    public function creditoNuevo()
    {
        return $this->belongsTo(Credito::class, 'id_credito_nuevo');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
