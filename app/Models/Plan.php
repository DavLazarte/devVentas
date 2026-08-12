<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $table = 'planes';

    protected $fillable = [
        'nombre',
        'slug',
        'precio_mensual',
        'max_productos',
        'tiene_pos',
        'tiene_clientes',
        'tiene_caja',
        'tiene_creditos',
        'tiene_reportes',
        'destacable',
        'recibe_pedidos',
        'descripcion',
        'estado',
        'orden',
    ];

    protected $casts = [
        'precio_mensual' => 'decimal:2',
        'tiene_pos' => 'boolean',
        'tiene_clientes' => 'boolean',
        'tiene_caja' => 'boolean',
        'tiene_creditos' => 'boolean',
        'tiene_reportes' => 'boolean',
        'destacable' => 'boolean',
        'recibe_pedidos' => 'boolean',
        'estado' => 'boolean',
    ];

    public function locales()
    {
        return $this->hasMany(Local::class, 'plan_id');
    }

    /**
     * Check if the plan allows a specific feature
     */
    public function allows(string $feature): bool
    {
        $featureMap = [
            'pos' => 'tiene_pos',
            'clientes' => 'tiene_clientes',
            'caja' => 'tiene_caja',
            'creditos' => 'tiene_creditos',
            'reportes' => 'tiene_reportes',
            'destacado' => 'destacable',
            'pedidos' => 'recibe_pedidos',
        ];

        $column = $featureMap[$feature] ?? null;
        return $column ? (bool) $this->{$column} : false;
    }

    /**
     * Check if adding one more product is within the plan's limit
     */
    public function canAddProduct(int $currentCount): bool
    {
        if (is_null($this->max_productos)) return true; // Ilimitado
        return $currentCount < $this->max_productos;
    }
}
