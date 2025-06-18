<?php

namespace App\Http\Livewire\Feed;

use Livewire\Component;
use App\Models\Articulo;
use App\Models\Servicio;
use Illuminate\Support\Collection;
use TCG\Voyager\Facades\Voyager;

class Products extends Component
{
    public $search = '';
    public $page = 1;
    public $perPage = 12;
    public $products;
    public $services;
    public $activeTab = 'products'; // 'products' o 'services'
    public $tab = 'products'; // Para el queryString
    public $hasMorePages = true;

    protected $queryString = ['search', 'tab'];

    // Debounce para la búsqueda
    protected $updatesQueryString = ['search'];

    public function mount()
    {
        if (request()->has('tab')) {
            $this->tab = request()->tab;
            $this->activeTab = request()->tab;
        }
        $this->loadItems();
    }

    public function updatedSearch()
    {
        $this->resetPage();
        $this->loadItems();
    }

    public function updatedActiveTab()
    {
        $this->tab = $this->activeTab;
        $this->resetPage();
        $this->loadItems();
    }

    public function resetPage()
    {
        $this->page = 1;
        $this->products = collect();
        $this->services = collect();
        $this->hasMorePages = true;
    }

    public function loadMore()
    {
        $this->page++;
        $this->loadItems();
    }

    protected function loadItems()
    {
        if ($this->activeTab === 'products') {
            $this->loadProducts();
        } else {
            $this->loadServices();
        }
    }

    protected function loadProducts()
    {
        // Construir la consulta base con eager loading optimizado
        $query = Articulo::query()
            ->with([
                'categoria:id_categoria,nombre',
                'local:id,nombre'
            ])
            ->where('estado', 1)
            ->where('mostrar_feed', 1);

        // Aplicar búsqueda si existe
        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nombre', 'like', $searchTerm)
                    ->orWhere('descripcion', 'like', $searchTerm)
                    ->orWhereHas('categoria', function ($q) use ($searchTerm) {
                        $q->where('nombre', 'like', $searchTerm);
                    });
            });
        }

        // Obtener el total de registros para saber si hay más páginas
        $total = $query->count();

        // Obtener los registros de la página actual con ordenamiento optimizado
        $newProducts = $query->skip(($this->page - 1) * $this->perPage)
            ->take($this->perPage)
            ->orderBy('destacado', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Procesar las imágenes para todos los productos
        $processedProducts = $newProducts->map(function ($product) {
            $product->imagen_url = $product->imagen
                ? Voyager::image($product->imagen)
                : asset('images/placeholder-product.jpg');
            return $product;
        });

        // Si es la primera página, reemplazar la colección
        if ($this->page === 1) {
            $this->products = $processedProducts;
        } else {
            // Si no es la primera página, crear una nueva colección con todos los items
            $allProducts = $this->products->merge($processedProducts);
            $this->products = $allProducts->map(function ($product) {
                $product->imagen_url = $product->imagen
                    ? Voyager::image($product->imagen)
                    : asset('images/placeholder-product.jpg');
                return $product;
            });
        }

        // Verificar si hay más páginas
        $this->hasMorePages = ($this->page * $this->perPage) < $total;
    }

    protected function loadServices()
    {
        // Construir la consulta base con eager loading optimizado
        $query = Servicio::query()
            ->with([
                'categoria:id_categoria,nombre',
                'local:id,nombre'
            ])
            ->where('estado', 1)
            ->where('mostrar_feed', 1);

        // Aplicar búsqueda si existe
        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nombre', 'like', $searchTerm)
                    ->orWhere('descripcion', 'like', $searchTerm)
                    ->orWhereHas('categoria', function ($q) use ($searchTerm) {
                        $q->where('nombre', 'like', $searchTerm);
                    });
            });
        }

        // Obtener el total de registros para saber si hay más páginas
        $total = $query->count();

        // Obtener los registros de la página actual con ordenamiento optimizado
        $newServices = $query->skip(($this->page - 1) * $this->perPage)
            ->take($this->perPage)
            ->orderBy('destacado', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Procesar las imágenes para todos los servicios
        $processedServices = $newServices->map(function ($service) {
            $service->imagen_url = $service->imagen
                ? Voyager::image($service->imagen)
                : asset('images/placeholder-service.jpg');
            return $service;
        });

        // Si es la primera página, reemplazar la colección
        if ($this->page === 1) {
            $this->services = $processedServices;
        } else {
            // Si no es la primera página, crear una nueva colección con todos los items
            $allServices = $this->services->merge($processedServices);
            $this->services = $allServices->map(function ($service) {
                $service->imagen_url = $service->imagen
                    ? Voyager::image($service->imagen)
                    : asset('images/placeholder-service.jpg');
                return $service;
            });
        }

        // Verificar si hay más páginas
        $this->hasMorePages = ($this->page * $this->perPage) < $total;
    }

    public function render()
    {
        return view('livewire.feed.products')->layout('layouts.app');
    }
}
