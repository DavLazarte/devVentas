<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticuloVariante extends Model
{
    use HasFactory;

    protected $table = 'articulo_variantes';
    protected $primaryKey = 'id_variante';

    protected $fillable = [
        'idarticulo',
        'sku',
        'precio_unitario',
        'stock',
        'imagen',
        'descripcion_variante',
        'estado',
        'es_variante_principal',
        // Campos para venta por peso/volumen
        'stock_decimal',
        'tipo_venta',
        'unidad_medida',
        'precios_por_cantidad'
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
        'stock' => 'integer',
        'es_variante_principal' => 'boolean',
        'stock_decimal' => 'decimal:3',
        'precios_por_cantidad' => 'array'
    ];

    protected $appends = ['imagen_url', 'tiene_stock'];

    // Relación con Articulo
    public function articulo()
    {
        return $this->belongsTo(Articulo::class, 'idarticulo', 'idarticulo');
    }

    // Relación con valores de atributos a través de la tabla pivote
    public function atributoValores()
    {
        return $this->belongsToMany(
            AtributoValor::class,
            'variante_atributo_valores',
            'id_variante',
            'id_valor'
        )->with('atributo');
    }

    // Accessor para URL de imagen
    public function getImagenUrlAttribute()
    {
        if ($this->imagen) {
            return asset('storage/' . $this->imagen);
        }

        // Verifica si la relación 'articulo' ha sido cargada
        if ($this->relationLoaded('articulo') && $this->articulo) {
            // Accede directamente a la propiedad 'imagen' del modelo padre, no al accesorio 'imagen_url'
            return $this->articulo->imagen ? asset('storage/' . $this->articulo->imagen) : asset('images/default-product.jpg');
        }

        return asset('images/default-product.jpg');
    }

    // Accessor para verificar stock
    public function getTieneStockAttribute()
    {
        // Si es venta por peso/volumen, verificar stock_decimal
        if ($this->tipo_venta === 'peso' || $this->tipo_venta === 'volumen') {
            return ($this->stock_decimal ?? 0) > 0;
        }
        return $this->stock > 0;
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    public function scopeConStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    public function scopeVariantePrincipal($query)
    {
        return $query->where('es_variante_principal', true);
    }

    // Helper para obtener la descripción de la variante basada en sus atributos
    public function generarDescripcionVariante()
    {
        $descripciones = [];

        foreach ($this->atributoValores as $atributoValor) {
            $descripciones[] = $atributoValor->atributo->nombre . ': ' . $atributoValor->valor;
        }

        return implode(', ', $descripciones);
    }

    // Método para actualizar la descripción automáticamente
    public function actualizarDescripcionVariante()
    {
        $this->descripcion_variante = $this->generarDescripcionVariante();
        $this->save();
    }

    // Helper para generar SKU automático
    public static function generarSku($articulo, $atributoValores = [])
    {
        $baseSku = $articulo->codigo ?? 'ART-' . $articulo->idarticulo;

        if (!empty($atributoValores)) {
            $sufijos = [];
            foreach ($atributoValores as $valor) {
                // CAMBIAR ESTA LÍNEA - $valor ya es un string, no un objeto
                $sufijos[] = strtoupper(substr($valor, 0, 3));
            }
            $baseSku .= '-' . implode('-', $sufijos);
        }

        // Verificar que el SKU sea único
        $contador = 1;
        $skuOriginal = $baseSku;

        while (self::where('sku', $baseSku)->exists()) {
            $baseSku = $skuOriginal . '-' . $contador;
            $contador++;
        }

        return $baseSku;
    }
}
