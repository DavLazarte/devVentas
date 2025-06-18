<?php

namespace App\Http\Livewire\Feed;

use App\Models\Category;
use App\Models\Articulo;
use App\Models\Local;
use Livewire\Component;
use TCG\Voyager\Facades\Voyager;
use Illuminate\Support\Facades\Cache;

class Home extends Component
{
    public $categoriaSeleccionada;
    public $subcategoriaSeleccionada;
    public $localesDestacados;
    public $productosDestacados;
    public $globalSearch = '';

    protected $queryString = ['globalSearch'];

    public function mount()
    {
        $this->loadLocalesDestacados();
        $this->loadProductosDestacados();
    }

    public function updatedGlobalSearch()
    {
        // Si hay una búsqueda, redirigir a la página de tiendas con el término de búsqueda
        if ($this->globalSearch) {
            return redirect()->route('all-shops', ['search' => $this->globalSearch]);
        }
    }

    public function loadProductosDestacados()
    {
        $this->productosDestacados = Articulo::with(['categoria', 'local'])
            ->destacados() // Usa el scope que ya definiste
            ->where('estado', 'activo')
            ->where('mostrar_feed', true)
            ->orderBy('created_at', 'desc') // O usa 'precio_unitario' para ordenar
            ->limit(8) // Ajusta según necesidad
            ->get()
            ->map(function ($producto) {
                // Prepara la URL de la imagen (compatible con Voyager)
                $producto->imagen_url = $producto->imagen
                    ? Voyager::image($producto->imagen)
                    : asset('images/placeholder-product.jpg');
                return $producto;
            });
    }

    public function loadLocalesDestacados()
    {
        $this->localesDestacados = Local::with(['user', 'categories', 'subcategories'])
            ->where('destacado', true) // Filtra solo destacados
            ->where('mostrar_feed', true)
            ->where('estado', 'activo') // Opcional: solo locales activos
            ->orderBy('rating_promedio', 'desc') // Ordena por rating
            ->limit(10) // Limita resultados
            ->get();
    }

    public function render()
    {
        $categorias = Cache::remember('categorias_activas', now()->addHours(12), function () {
            return Category::with(['subcategories', 'locales'])
                ->where('state', 'active')
                ->get();
        });

        return view('livewire.feed.home', ['categorias' => $categorias])->layout('layouts.app');
    }
}
