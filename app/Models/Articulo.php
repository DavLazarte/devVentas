<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Articulo extends Model
{
    use HasFactory;

    protected $table = 'articulos';
    protected $primaryKey = 'idarticulo';
    protected $fillable = [
        'idcategoria',
        'codigo',
        'nombre',
        'stock',
        'descripcion',
        'imagen',
        'precio_unitario',
        'precio_costo',
        'estado',
        'mostrar_feed',
        'destacado',
        'id_local',
        'tiene_variantes',
        // Campos para venta por peso/volumen
        'tipo_venta',
        'unidad_medida',
        'precio_por_unidad_medida',
        'stock_decimal',
        'precios_por_cantidad'
    ];

    protected $appends = ['imagen_url', 'precio_minimo', 'precio_maximo', 'stock_total'];

    protected $casts = [
        'tiene_variantes' => 'boolean',
        'destacado' => 'boolean',
        'mostrar_feed' => 'boolean',
        'precio_por_unidad_medida' => 'decimal:2',
        'precio_costo' => 'decimal:2',
        'stock_decimal' => 'decimal:3',
        'precios_por_cantidad' => 'array'
    ];

    public function getImagenUrlAttribute()
    {
        if (!$this->imagen) return null;
        // Limpiamos la ruta por si quedó guardada con /storage/ de pruebas anteriores
        $cleanPath = str_replace('/storage/', '', $this->imagen);
        return asset('storage/' . $cleanPath);
    }

    // RELACIONES EXISTENTES
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'idcategoria', 'id_categoria');
    }

    public function local()
    {
        return $this->belongsTo(Local::class, 'id_local');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class, 'idarticulo', 'idarticulo');
    }

    // NUEVAS RELACIONES PARA VARIANTES
    public function variantes()
    {
        return $this->hasMany(ArticuloVariante::class, 'idarticulo', 'idarticulo');
    }

    public function variantesActivas()
    {
        return $this->hasMany(ArticuloVariante::class, 'idarticulo', 'idarticulo')
            ->where('estado', 'activo');
    }

    public function variantePrincipal()
    {
        return $this->hasOne(ArticuloVariante::class, 'idarticulo', 'idarticulo')
            ->where('es_variante_principal', true)
            ->where('estado', 'activo');
    }

    // ACCESSORS PARA MANEJAR PRECIOS Y STOCK CON VARIANTES
    public function getPrecioMinimoAttribute()
    {
        if (!$this->tiene_variantes) {
            return $this->precio_unitario;
        }

        return $this->variantesActivas->min('precio_unitario') ?? $this->precio_unitario;
    }

    public function getPrecioMaximoAttribute()
    {
        if (!$this->tiene_variantes) {
            return $this->precio_unitario;
        }

        return $this->variantesActivas->max('precio_unitario') ?? $this->precio_unitario;
    }

    public function getStockTotalAttribute()
    {
        if (!$this->tiene_variantes) {
            // Si es venta por peso, retornar stock_decimal
            if ($this->tipo_venta === 'peso' || $this->tipo_venta === 'volumen') {
                return $this->stock_decimal ?? 0;
            }
            return $this->stock;
        }

        if ($this->tipo_venta === 'peso' || $this->tipo_venta === 'volumen') {
            return $this->variantesActivas->sum('stock_decimal');
        }

        return $this->variantesActivas->sum('stock');
    }

    // Accessor para mostrar precio formateado según tipo de venta
    public function getPrecioDisplayAttribute()
    {
        if ($this->tipo_venta === 'peso' || $this->tipo_venta === 'volumen') {
            return '$' . number_format($this->precio_por_unidad_medida, 2) . '/' . $this->unidad_medida;
        }
        return '$' . number_format($this->precio_unitario, 2);
    }

    // SCOPES EXISTENTES
    public function scopeDestacados($query)
    {
        return $query->where('destacado', true);
    }

    // NUEVOS SCOPES
    public function scopeConVariantes($query)
    {
        return $query->where('tiene_variantes', true);
    }

    public function scopeSinVariantes($query)
    {
        return $query->where('tiene_variantes', false);
    }

    public function scopeConStock($query)
    {
        return $query->where(function ($query) {
            // Productos sin variantes con stock (unidad)
            $query->where('tiene_variantes', false)
                ->where('tipo_venta', 'unidad')
                ->where('stock', '>', 0);
        })->orWhere(function ($query) {
            // Productos sin variantes con stock_decimal (peso/volumen)
            $query->where('tiene_variantes', false)
                ->whereIn('tipo_venta', ['peso', 'volumen'])
                ->where('stock_decimal', '>', 0);
        })->orWhere(function ($query) {
            // Productos con variantes (unidad) que tienen stock
            $query->where('tiene_variantes', true)
                ->where('tipo_venta', 'unidad')
                ->whereHas('variantesActivas', function ($subQuery) {
                    $subQuery->where('stock', '>', 0);
                });
        })->orWhere(function ($query) {
            // Productos con variantes (peso/volumen) que tienen stock_decimal
            $query->where('tiene_variantes', true)
                ->whereIn('tipo_venta', ['peso', 'volumen'])
                ->whereHas('variantesActivas', function ($subQuery) {
                    $subQuery->where('stock_decimal', '>', 0);
                });
        });
    }

    // MÉTODOS HELPER
    public function tieneStock()
    {
        if (!$this->tiene_variantes) {
            if ($this->tipo_venta === 'peso' || $this->tipo_venta === 'volumen') {
                return ($this->stock_decimal ?? 0) > 0;
            }
            return $this->stock > 0;
        }

        if ($this->tipo_venta === 'peso' || $this->tipo_venta === 'volumen') {
            return $this->variantesActivas->where('stock_decimal', '>', 0)->count() > 0;
        }
        return $this->variantesActivas->where('stock', '>', 0)->count() > 0;
    }

    public function obtenerVariantesPorAtributo()
    {
        if (!$this->tiene_variantes) {
            return [];
        }

        $variantes = $this->variantesActivas->load('atributoValores.atributo');
        $atributos = [];

        foreach ($variantes as $variante) {
            foreach ($variante->atributoValores as $atributoValor) {
                $nombreAtributo = $atributoValor->atributo->nombre;

                if (!isset($atributos[$nombreAtributo])) {
                    $atributos[$nombreAtributo] = [];
                }

                $atributos[$nombreAtributo][] = $atributoValor;
            }
        }

        return $atributos;
    }

    public function convertirAVariantes($atributos = [])
    {
        // Método para convertir un producto simple a uno con variantes
        $this->tiene_variantes = true;
        $this->save();

        // Crear variante principal con los datos actuales
        $variantePrincipal = ArticuloVariante::create([
            'idarticulo' => $this->idarticulo,
            'sku' => $this->codigo ?? ArticuloVariante::generarSku($this),
            'precio_unitario' => $this->precio_unitario,
            'stock' => $this->stock,
            'imagen' => $this->imagen,
            'estado' => $this->estado,
            'es_variante_principal' => true,
            'descripcion_variante' => 'Variante principal'
        ]);

        return $variantePrincipal;
    }
}
