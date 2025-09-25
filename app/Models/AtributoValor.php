<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AtributoValor extends Model
{
    use HasFactory;

    protected $table = 'atributo_valores';
    protected $primaryKey = 'id_valor';
    
    protected $fillable = [
        'id_atributo',
        'valor',
        'color_hex',
        'imagen',
        'orden',
        'estado'
    ];

    protected $casts = [
        'orden' => 'integer'
    ];

    protected $appends = ['imagen_url'];

    // Relación con Atributo
    public function atributo()
    {
        return $this->belongsTo(Atributo::class, 'id_atributo', 'id_atributo');
    }

    // Relación con variantes a través de la tabla pivote
    public function variantes()
    {
        return $this->belongsToMany(
            ArticuloVariante::class, 
            'variante_atributo_valores', 
            'id_valor', 
            'id_variante'
        );
    }

    // Accessor para URL de imagen
    public function getImagenUrlAttribute()
    {
        if ($this->imagen) {
            return asset('storage/' . $this->imagen);
        }
        
        // Si es color y no tiene imagen, generar un color sólido
        if ($this->atributo && $this->atributo->tipo === 'color' && $this->color_hex) {
            return null; // Se manejará con CSS
        }
        
        return asset('images/default-swatch.png');
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    public function scopePorAtributo($query, $idAtributo)
    {
        return $query->where('id_atributo', $idAtributo);
    }

    // Helper para mostrar el valor formateado
    public function getValorFormateadoAttribute()
    {
        if ($this->atributo && $this->atributo->tipo === 'color') {
            return $this->valor . ' ' . ($this->color_hex ?? '');
        }
        
        return $this->valor;
    }
}
