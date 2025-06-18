<?php

namespace App\Http\Livewire\Feed;

use Livewire\Component;
use App\Models\Local;
use App\Models\Subcategory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Stores extends Component
{
    public $selectedSubcategory = null;
    public $category = null;
    public $search = '';
    public $page = 1;
    public $perPage = 12;
    public $stores;
    public $hasMorePages = true;

    protected $queryString = ['search', 'selectedSubcategory', 'category'];

    protected $listeners = [
        'filterBySubcategory' => 'filterBySubcategory',
        '$refresh' => '$refresh'
    ];

    // Debounce para la búsqueda
    protected $updatesQueryString = ['search'];

    public function mount()
    {
        if (request()->has('selectedSubcategory')) {
            $this->selectedSubcategory = request()->selectedSubcategory;
        }
        if (request()->has('category')) {
            $this->category = request()->category;
        }
        $this->loadStores();
    }

    public function filterBySubcategory($subcategoryId)
    {
        $this->selectedSubcategory = $subcategoryId;
        $this->resetPage();
        $this->loadStores();
    }

    public function updatedSearch()
    {
        $this->resetPage();
        $this->loadStores();
    }

    public function resetPage()
    {
        $this->page = 1;
        $this->stores = collect();
        $this->hasMorePages = true;
    }

    public function loadMore()
    {
        $this->page++;
        $this->loadStores();
    }

    protected function loadStores()
    {
        // Construir la consulta base con eager loading optimizado
        $query = Local::query()
            ->select([
                'locales.*',
                DB::raw('(SELECT COUNT(*) FROM category_local WHERE category_local.local_id = locales.id) as categories_count')
            ])
            ->with([
                'categories:id,name',
                'subcategories:id,name'
            ])
            ->where('estado', 'activo')
            ->where('mostrar_feed', true); // o ->where('mostrar_feed', 1)

        // Aplicar búsqueda si existe
        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nombre', 'like', $searchTerm)
                    ->orWhere('descripcion', 'like', $searchTerm)
                    ->orWhereHas('categories', function ($q) use ($searchTerm) {
                        $q->where('name', 'like', $searchTerm);
                    });
            });
        }

        // Aplicar filtro de categoría si existe
        if ($this->category) {
            $query->whereHas('categories', function ($q) {
                $q->where('categories.id', $this->category);
            });
        }

        // Aplicar filtro de subcategoría si existe
        if ($this->selectedSubcategory) {
            $query->whereHas('subcategories', function ($q) {
                $q->where('subcategories.id', $this->selectedSubcategory);
            });
        }

        // Obtener el total de registros para saber si hay más páginas
        $total = $query->count();

        // Obtener los registros de la página actual con ordenamiento optimizado
        $newStores = $query->skip(($this->page - 1) * $this->perPage)
            ->take($this->perPage)
            ->orderBy('destacado', 'desc')
            ->orderBy('rating_promedio', 'desc')
            ->get();

        // Si es la primera página, reemplazar la colección
        if ($this->page === 1) {
            $this->stores = $newStores;
        } else {
            // Si no es la primera página, agregar a la colección existente
            $this->stores = $this->stores->concat($newStores);
        }

        // Verificar si hay más páginas
        $this->hasMorePages = ($this->page * $this->perPage) < $total;
    }

    public function render()
    {
        // Cargar las subcategorías solo una vez y cachearlas
        $subcategories = cache()->remember('subcategories', 3600, function () {
            return Subcategory::select('id', 'name', 'category_id')
                ->with('category:id,name')
                ->get();
        });

        // Si hay una categoría seleccionada, filtrar las subcategorías
        if ($this->category) {
            $subcategories = $subcategories->where('category_id', $this->category);
        }

        return view('livewire.feed.stores', [
            'subcategories' => $subcategories
        ])->layout('layouts.app');
    }
}
