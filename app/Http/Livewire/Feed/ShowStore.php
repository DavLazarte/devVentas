<?php

namespace App\Http\Livewire\Feed;

use Livewire\Component;
use App\Models\Local;
use App\Models\Articulo;
use App\Models\Servicio;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

class ShowStore extends Component
{
    public $local;
    public $isFavorite = false;
    public $isFollowing = false;
    public $activeTab = 'productos';
    public $perPage = 12;
    public $page = 1;
    public $hasMorePages = true;
    public $products;
    public $services;

    protected $queryString = ['activeTab'];

    public function mount($slug)
    {
        $this->local = Cache::remember('local.' . $slug, 3600, function () use ($slug) {
            return Local::where('slug', $slug)
                ->with(['categories', 'subcategories'])
                ->firstOrFail();
        });
        $this->products = collect();
        $this->services = collect();
        $this->loadItems();
    }

    public function toggleFavorite()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Implementar lógica de favoritos
        $this->isFavorite = !$this->isFavorite;
    }

    public function toggleFollow()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Implementar lógica de seguimiento
        $this->isFollowing = !$this->isFollowing;
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
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

    public function loadItems()
    {
        if ($this->activeTab === 'productos') {
            $this->loadProducts();
        } elseif ($this->activeTab === 'servicios') {
            $this->loadServices();
        } else {
            $this->loadProducts();
            $this->loadServices();
        }
    }

    public function loadProducts()
    {
        $query = Articulo::where('id_local', $this->local->id)
            ->where('estado', true)
            ->where('mostrar_feed', true);
        $total = $query->count();
        $newProducts = $query->skip(($this->page - 1) * $this->perPage)
            ->take($this->perPage)
            ->orderBy('destacado', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        if ($this->page === 1) {
            $this->products = $newProducts;
        } else {
            $this->products = $this->products->concat($newProducts);
        }
        $this->hasMorePages = ($this->page * $this->perPage) < $total;
    }

    public function loadServices()
    {
        $query = Servicio::where('id_local', $this->local->id)
            ->where('estado', true)
            ->where('mostrar_feed', true);
        $total = $query->count();
        $newServices = $query->skip(($this->page - 1) * $this->perPage)
            ->take($this->perPage)
            ->orderBy('destacado', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        if ($this->page === 1) {
            $this->services = $newServices;
        } else {
            $this->services = $this->services->concat($newServices);
        }
        $this->hasMorePages = ($this->page * $this->perPage) < $total;
    }

    public function render()
    {
        return view('livewire.feed.show-store', [
            'products' => $this->products,
            'services' => $this->services
        ])->layout('layouts.app');
    }
}
