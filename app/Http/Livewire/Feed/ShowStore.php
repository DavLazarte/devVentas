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
    public $perPage = 6;

    protected $queryString = ['activeTab'];

    public function mount($slug)
    {
        $this->local = Cache::remember('local.' . $slug, 3600, function () use ($slug) {
            return Local::where('slug', $slug)
                ->with(['categories', 'subcategories'])
                ->firstOrFail();
        });

        // Aquí podrías cargar el estado de favorito y seguimiento del usuario actual
        // $this->isFavorite = auth()->user() ? $this->local->favoritos()->where('user_id', auth()->id())->exists() : false;
        // $this->isFollowing = auth()->user() ? $this->local->seguidores()->where('user_id', auth()->id())->exists() : false;
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
    }

    public function render()
    {
        $products = collect();
        $services = collect();

        if ($this->activeTab === 'productos') {
            $products = Articulo::where('id_local', $this->local->id)
                ->where('estado', true)
                ->where('mostrar_feed', true)
                ->take($this->perPage)
                ->get();
        } elseif ($this->activeTab === 'servicios') {
            $services = Servicio::where('id_local', $this->local->id)
                ->where('estado', true)
                ->where('mostrar_feed', true)
                ->take($this->perPage)
                ->get();
        } else {
            // Tab "Todo" - Combinamos ambos y tomamos los primeros 10
            $products = Articulo::where('id_local', $this->local->id)
                ->where('estado', true)
                ->where('mostrar_feed', true)
                ->take(5)
                ->get();

            $services = Servicio::where('id_local', $this->local->id)
                ->where('estado', true)
                ->where('mostrar_feed', true)
                ->take(5)
                ->get();
        }

        return view('livewire.feed.show-store', [
            'products' => $products,
            'services' => $services
        ])->layout('layouts.app');
    }
}
