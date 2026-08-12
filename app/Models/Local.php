<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Models\User as VoyagerUser;

class Local extends Model
{
    use HasFactory;
    protected $table = 'locales';

    protected $fillable = [
    'id_user',
    'nombre',
    'slug',
    'direccion',
    'localidad',
    'provincia',
    'telefono',
    'email',
    'estado',
    'mostrar_feed',
    'tipo',
    'plan',
    'plan_id',
    'descripcion',
    'horario',
    'latitud',
    'longitud',
    'foto_portada',
    'foto_logo',
    'rating_promedio',
    'destacado',
    'sitio_web',
    'redes_sociales',
    'siempre_abierto',
    'de_turno',
];

    protected $appends = ['foto_portada_url', 'foto_logo_url'];

    public function getFotoPortadaUrlAttribute()
    {
        if (!$this->foto_portada) return null;
        // Si ya es una URL externa, retornarla
        if (filter_var($this->foto_portada, FILTER_VALIDATE_URL)) return $this->foto_portada;
        return asset('storage/' . $this->foto_portada);
    }

    public function getFotoLogoUrlAttribute()
    {
        if (!$this->foto_logo) return null;
        if (filter_var($this->foto_logo, FILTER_VALIDATE_URL)) return $this->foto_logo;
        return asset('storage/' . $this->foto_logo);
    }

    public function user()
    {
        return $this->belongsTo(VoyagerUser::class, 'id_user');
    }

    public function categories() {
        return $this->belongsToMany(Category::class, 'category_local');
    }

    public function subcategories() {
        return $this->belongsToMany(Subcategory::class, 'local_subcategory');
    }

    public function planInfo() {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    public function anuncios() {
        return $this->hasMany(Anuncio::class, 'local_id');
    }

    /**
     * Check if the local's plan allows a specific feature
     */
    public function planAllows(string $feature): bool
    {
        $plan = $this->planInfo;
        if (!$plan) return false;
        return $plan->allows($feature);
    }

    /**
     * Check if the local can add more products
     */
    public function canAddProduct(): bool
    {
        $plan = $this->planInfo;
        $currentCount = \App\Models\Articulo::where('id_local', $this->id)->count();
        
        if (!$plan) {
            // Fallback for older locales that only have string 'plan' instead of 'plan_id'
            $planString = $this->plan ?? 'free';
            $maxLimit = match($planString) {
                'free' => 15,
                'basic' => 2000, // example
                'premium' => 999999, // example
                default => 15,
            };
            return $currentCount < $maxLimit;
        }

        return $plan->canAddProduct($currentCount);
    }
}
