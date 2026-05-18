<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SocioController;
use App\Http\Controllers\Api\ArticuloController;
use App\Http\Controllers\Api\VentaController;
use App\Http\Controllers\Api\PedidoController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\CajaController;
use App\Http\Controllers\Api\SuperadminController;
use App\Http\Controllers\Api\PlanCreditoController;
use App\Http\Controllers\Api\CreditoController;
use App\Http\Controllers\Api\PagoCuotaController;
use App\Http\Controllers\Api\OpenFoodFactsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Auth público (sin token)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/images/perfiles/{filename}', [SocioController::class, 'serveImage']);

// Rutas protegidas (requieren token Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // ──────────────────────────────────────────────
    // Súper Administración Global
    // ──────────────────────────────────────────────
    Route::prefix('superadmin')->group(function () {
        Route::get('/locales', [SuperadminController::class, 'getLocales']);
        Route::post('/locales', [SuperadminController::class, 'storeTenant']);
        Route::delete('/locales/{id}', [SuperadminController::class, 'deleteLocal']);
    });

    // ──────────────────────────────────────────────
    // POS Vendedor
    // ──────────────────────────────────────────────

    // Artículos / Stock
    Route::get('/products/barcode/{barcode}', [OpenFoodFactsController::class, 'findByBarcode']);
    Route::get('/articulos', [ArticuloController::class, 'index']);
    Route::get('/articulos/{id}', [ArticuloController::class, 'show']);
    Route::post('/articulos', [ArticuloController::class, 'store']);
    Route::put('/articulos/{id}', [ArticuloController::class, 'update']);
    Route::delete('/articulos/{id}', [ArticuloController::class, 'destroy']);
    Route::patch('articulos/{id}/stock', [ArticuloController::class, 'updateStock']);
    Route::patch('articulos/{id}/variantes/{variantId}/stock', [ArticuloController::class, 'updateVariantStock']);

    // Categorías
    Route::get('/categorias', [\App\Http\Controllers\Api\CategoriaController::class, 'index']);
    Route::post('/categorias', [\App\Http\Controllers\Api\CategoriaController::class, 'store']);

    // Atributos y Variantes
    Route::get('/atributos', [\App\Http\Controllers\Api\AtributoController::class, 'index']);
    Route::post('/atributos', [\App\Http\Controllers\Api\AtributoController::class, 'store']);
    Route::post('/atributos/{id}/valores', [\App\Http\Controllers\Api\AtributoController::class, 'storeValor']);

    // Ventas
    Route::get('/ventas/dashboard', [VentaController::class, 'dashboard']);
    Route::get('/ventas', [VentaController::class, 'index']);
    Route::post('/ventas', [VentaController::class, 'store']);

    // Pedidos
    Route::get('/pedidos', [PedidoController::class, 'index']);
    Route::post('/pedidos', [PedidoController::class, 'store']);
    Route::patch('/pedidos/{id}', [PedidoController::class, 'update']);
    Route::patch('/pedidos/{id}/estado', [PedidoController::class, 'updateEstado']);
    Route::post('/pedidos/{id}/entregar', [PedidoController::class, 'entregar']);
    Route::delete('/pedidos/{id}', [PedidoController::class, 'destroy']);

    // Clientes POS (cuenta corriente)
    Route::get('/clientes-pos', [ClienteController::class, 'index']);
    Route::post('/clientes-pos', [ClienteController::class, 'store']);
    Route::put('/clientes-pos/{id}', [ClienteController::class, 'update']);
    Route::post('/clientes-pos/{id}/transacciones', [ClienteController::class, 'createTransaction']);
    Route::post('/clientes-pos/{id}/pago', [ClienteController::class, 'registrarPago']);

    // Caja / Cashflow
    Route::get('/caja', [CajaController::class, 'index']);
    Route::post('/caja', [CajaController::class, 'store']);

    // ──────────────────────────────────────────────
    // Financiera (Créditos y Préstamos)
    // ──────────────────────────────────────────────
    Route::apiResource('planes-credito', PlanCreditoController::class);
    
    Route::get('/creditos/dashboard', [CreditoController::class, 'dashboard']);
    Route::get('/creditos', [CreditoController::class, 'index']);
    Route::post('/creditos', [CreditoController::class, 'store']);
    Route::get('/creditos/{id}', [CreditoController::class, 'show']);
    Route::put('/creditos/{id}', [CreditoController::class, 'update']);
    Route::delete('/creditos/{id}', [CreditoController::class, 'destroy']);
    Route::patch('/creditos/{id}/cancelar', [CreditoController::class, 'cancelar']);
    Route::patch('/creditos/{id}/refinanciar', [CreditoController::class, 'marcarComoRefinanciado']);
    
    Route::get('/cobradores-usuarios', [CreditoController::class, 'getCobradores']);
    Route::post('/creditos/{id}/pagos', [PagoCuotaController::class, 'store']);

    // ──────────────────────────────────────────────
    // Socios / Gym (legacy)
    // ──────────────────────────────────────────────
    Route::get('/socios', [SocioController::class, 'index']);
    Route::post('/socios', [SocioController::class, 'store']);
    Route::get('/socios/{id}', [SocioController::class, 'show']);
    Route::put('/socios/{id}', [SocioController::class, 'update']);
    Route::delete('/socios/{id}', [SocioController::class, 'destroy']);
    Route::post('/socios/{id}/create-user', [SocioController::class, 'createUser']);
    Route::get('/perfil', [SocioController::class, 'getProfile']);
    Route::post('/perfil', [SocioController::class, 'updateProfile']);

    // Servicios (Planes, Clases)
    Route::apiResource('servicios', \App\Http\Controllers\Api\ServicioController::class);

    // Clases (Horarios/Templates)
    Route::get('/clases/instructor-stats', [\App\Http\Controllers\Api\ClaseGymController::class, 'getInstructorStats']);
    Route::apiResource('clases', \App\Http\Controllers\Api\ClaseGymController::class);

    // Reservas y Disponibilidad
    Route::get('/clases-disponibles', [\App\Http\Controllers\Api\ReservaGymController::class, 'getAvailableClasses']);
    Route::get('/asistencias', [\App\Http\Controllers\Api\AsistenciaGymController::class, 'index']);
    Route::post('/asistencias', [\App\Http\Controllers\Api\AsistenciaGymController::class, 'store']);
    Route::apiResource('reservas', \App\Http\Controllers\Api\ReservaGymController::class);

    // Membresías
    Route::apiResource('membresias', \App\Http\Controllers\Api\MembresiaController::class);
    Route::apiResource('pagos-gym', \App\Http\Controllers\Api\PagoGymController::class);

    // Dashboard Gym
    Route::get('/dashboard/stats', [\App\Http\Controllers\Api\DashboardController::class, 'getStats']);

    // Salidas Gym
    Route::apiResource('salidas-gym', \App\Http\Controllers\Api\SalidaGymController::class);
});
