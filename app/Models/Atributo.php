<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Atributo extends Model
{
    use HasFactory;

    protected $table = 'atributos';
    protected $primaryKey = 'id_atributo';
    
    protected $fillable = [
        'nombre',
        'tipo',
        'obligatorio',
        'id_local',
        'estado',
        'orden'
    ];

    protected $casts = [
        'obligatorio' => 'boolean',
        'orden' => 'integer'
    ];

    // Relación con Local
    public function local()
    {
        return $this->belongsTo(Local::class, 'id_local');
    }

    // Relación con AtributoValor
    public function valores()
    {
        return $this->hasMany(AtributoValor::class, 'id_atributo', 'id_atributo')
                    ->where('estado', 'activo')
                    ->orderBy('orden');
    }

    // Todos los valores (incluyendo inactivos)
    public function todosLosValores()
    {
        return $this->hasMany(AtributoValor::class, 'id_atributo', 'id_atributo')
                    ->orderBy('orden');
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    public function scopePorLocal($query, $idLocal)
    {
        return $query->where('id_local', $idLocal);
    }

    public function scopeObligatorios($query)
    {
        return $query->where('obligatorio', true);
    }

    // Accessor para tipos de atributos
    public function getTiposDisponiblesAttribute()
    {
        return [
            'color' => 'Color',
            'talla' => 'Talla',
            'texto' => 'Texto libre',
            'numero' => 'Número',
            'select' => 'Selección'
        ];
    }
}
