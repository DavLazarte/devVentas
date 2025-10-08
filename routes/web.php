<?php

use App\Http\Livewire\CategoriaLivewire;
use App\Http\Livewire\Feed\Home;
use App\Http\Livewire\Persona\PersonaLivewire;
use App\Http\Livewire\Articulo\ArticuloLivewire;
use App\Http\Livewire\Servicios;
use App\Http\Livewire\Pedidos;
use App\Http\Livewire\Caja\AdminCajas;
use App\Http\Livewire\CategoriaLocales;
use App\Http\Livewire\Resultados\LocalResults;
use App\Http\Livewire\Compras;
use App\Http\Livewire\Venta\Ventas;
use App\Http\Livewire\DashVentas;
use App\Http\Livewire\Feed\Stores;
use App\Http\Livewire\Feed\Products;
use App\Http\Controllers\OrderNotificationController;
use App\Http\Livewire\Feed\ProductDetail;
use App\Http\Livewire\Ingreso\IngresoComponent;
use App\Http\Livewire\Ingreso\DeudoresComponent;
use App\Http\Livewire\List\ListCompras;
use App\Http\Livewire\List\ListVentas;
use App\Http\Livewire\Salida\SalidaComponent;
use App\Models\Compra;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use TCG\Voyager\Facades\Voyager;
use App\Http\Livewire\Checkout;



Route::get('/', Home::class)->name('feed');
Route::get('/all-shops', Stores::class)->name('all-shops');
Route::get('/all-products', Products::class)->name('all-products');
Route::get('/store/{slug}', App\Http\Livewire\Feed\ShowStore::class)->name('store.show');
Route::get('/product/{type}/{id}', ProductDetail::class)->name('product.show');

// Route::get('/categoria/{slug}', CategoriaLocales::class);


Route::get('/terminos', function () {
    return view('terminos');
});
Route::get('/politicas', function () {
    return view('politicas');
});

Route::get('/checkout', Checkout::class)->name('checkout');
Route::get('/checkout/confirmation/{pedido}', function ($pedido) {
    $pedido = \App\Models\Pedido::with(['detalles.producto', 'detalles.variantesArticulos', 'local'])->findOrFail($pedido);
    return view('checkout/confirmation', ['pedido' => $pedido]);
})->name('checkout.confirmation');
Route::get('/booking/services', function () {
    return view('booking/services');
});
Route::get('/booking/datetime', function () {
    return view('booking/select-date');
});
Route::get('/booking/info', function () {
    return view('booking/info');
});
Route::get('/booking/confirmation', function () {
    return view('booking/confirmation');
});


Route::group(['prefix' => 'admin'], function () {
    Route::get('/storage-link', function () {
        Artisan::call('storage:link');
        return "Enlace de almacenamiento creado correctamente.";
    });
    Route::get('/optimizar', function () {
        Artisan::call('config:cache');
        Artisan::call('route:cache');
        Artisan::call('view:cache');
        Artisan::call('optimize');
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');

        return 'Optimización y limpieza de caché completada ✔';
    });

    Voyager::routes();


    Route::middleware(['admin.user'])->group(function () {
        Route::get('/sistema', DashVentas::class)->name('sistema');
        Route::get('/categorias', CategoriaLivewire::class)->name('categorias');
        Route::get('/articulos', ArticuloLivewire::class)->name('articulos');
        Route::get('/servicios', Servicios::class)->name('servicio');
        Route::get('/pedidos', Pedidos::class)->name('pedidos');
        Route::get('/personas', PersonaLivewire::class)->name('personas');
        Route::get('/ventas', Ventas::class)->name('ventas');
        Route::get('/compras', Compras::class)->name('compras');
        Route::get('/ingresos', IngresoComponent::class)->name('ingresos');
        Route::get('/ventas-saldos', DeudoresComponent::class)->name('saldos');
        Route::get('/salidas', SalidaComponent::class)->name('salidas');
        Route::get('/caja', AdminCajas::class)->name('caja');
        Route::get('/list-ventas', ListVentas::class)->name('/list-ventas');
        Route::get('/list-compras', ListCompras::class)->name('/list-compras');

        Route::view('/dashboard', 'dashboard')->name('dashboard');
        Route::view('/productos', 'ventas.productos.index')->name('productos');
        Route::get('/api/check-new-orders', [OrderNotificationController::class, 'checkNewOrders'])->name('check.new.orders');
    });
});

require __DIR__ . '/auth.php';
