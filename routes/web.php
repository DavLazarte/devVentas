<?php

use App\Http\Livewire\CategoriaLivewire;
use App\Http\Livewire\Persona\PersonaLivewire;
use App\Http\Livewire\Articulo\ArticuloLivewire;
use App\Http\Livewire\Caja\AdminCajas;
use App\Http\Livewire\Venta\Ventas;
use App\Http\Livewire\DashVentas;
use App\Http\Livewire\Ingreso\IngresoComponent;
use App\Http\Livewire\List\ListVentas;
use App\Http\Livewire\Salida\SalidaComponent;
use Illuminate\Support\Facades\Route;
use TCG\Voyager\Facades\Voyager;

Route::group(['prefix' => 'admin'], function () {
    Voyager::routes();


    Route::middleware(['admin.user'])->group(function () {
        Route::get('/sistema', DashVentas::class)->name('sistema');
        Route::get('/categorias', CategoriaLivewire::class)->name('categorias');
        Route::get('/articulos', ArticuloLivewire::class)->name('articulos');
        Route::get('/personas', PersonaLivewire::class)->name('personas');
        Route::get('/ventas', Ventas::class)->name('ventas');
        Route::get('/ingresos', IngresoComponent::class)->name('ingresos');
        Route::get('/salidas', SalidaComponent::class)->name('salidas');
        Route::get('/caja', AdminCajas::class)->name('caja');
        Route::get('/list-ventas', ListVentas::class)->name('list-ventas');

        Route::view('/dashboard', 'dashboard')->name('dashboard');
        Route::view('/productos', 'ventas.productos.index')->name('productos');
    });
});

require __DIR__ . '/auth.php';
