<?php

use App\Http\Livewire\MateriaPrima;
use App\Http\Livewire\CategoriaLivewire;
use App\Http\Livewire\Persona\PersonaLivewire;
use App\Http\Livewire\Articulo\ArticuloLivewire;
use App\Http\Livewire\Admin\Recetas;
use App\Http\Livewire\Venta\Ventas;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin/recetas', Recetas::class)->middleware(['auth'])->name('admin.recetas');

Route::get('/materias', MateriaPrima::class)->middleware(['auth'])->name('materias');
Route::get('/categorias', CategoriaLivewire::class)->middleware(['auth'])->name('categorias');
Route::get('/articulos', ArticuloLivewire::class)->middleware(['auth'])->name('articulos');
Route::get('/personas', PersonaLivewire::class)->middleware(['auth'])->name('personas');
Route::get('/ventas', Ventas::class)->middleware(['auth'])->name('ventas');
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');
Route::get('/sistema', function () {
    return view('ventas.dashventas');
})->middleware(['auth'])->name('sistema');
Route::get('/productos', function () {
    return view('ventas.productos.index');
})->middleware(['auth'])->name('productos');

require __DIR__ . '/auth.php';
