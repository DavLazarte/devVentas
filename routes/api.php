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
use App\Http\Controllers\Api\LocalConfigController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Auth público (sin token)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/registrar-negocio', [AuthController::class, 'registrarNegocio']);
Route::get('/auth/google', [\App\Http\Controllers\Api\GoogleAuthController::class, 'redirect']);
Route::get('/auth/google/callback', [\App\Http\Controllers\Api\GoogleAuthController::class, 'callback']);
Route::get('/images/perfiles/{filename}', [SocioController::class, 'serveImage']);

// ──────────────────────────────────────────────
// Marketplace Público (Tienda Dux)
// ──────────────────────────────────────────────
Route::prefix('tienda')->group(function () {
    Route::get('/categorias', [\App\Http\Controllers\Api\MarketplaceController::class, 'categorias']);
    Route::get('/anuncios', [\App\Http\Controllers\Api\MarketplaceController::class, 'anuncios']);
    Route::get('/locales', [\App\Http\Controllers\Api\MarketplaceController::class, 'locales']);
    Route::get('/locales/destacados', [\App\Http\Controllers\Api\MarketplaceController::class, 'destacados']);
    Route::get('/locales/recomendados', [\App\Http\Controllers\Api\MarketplaceController::class, 'recomendados']);
    Route::get('/locales/nuevos', [\App\Http\Controllers\Api\MarketplaceController::class, 'nuevos']);
    Route::get('/locales/nuevos-todos', [\App\Http\Controllers\Api\MarketplaceController::class, 'nuevosTodos']);
    Route::get('/locales/{slug}', [\App\Http\Controllers\Api\MarketplaceController::class, 'showLocal']);
    Route::get('/locales/{slug}/productos', [\App\Http\Controllers\Api\MarketplaceController::class, 'productosLocal']);
    Route::get('/productos/tendencias', [\App\Http\Controllers\Api\MarketplaceController::class, 'tendencias']);
    Route::get('/productos/{id}', [\App\Http\Controllers\Api\MarketplaceController::class, 'showProducto']);
    Route::post('/pedido', [\App\Http\Controllers\Api\MarketplaceController::class, 'createPedido']);
    Route::post('/pedidos/track', [\App\Http\Controllers\Api\MarketplaceController::class, 'trackPedidos']);
    Route::get('/cerca', [\App\Http\Controllers\Api\MarketplaceController::class, 'cercaTuyo']);
});

// Planes públicos (para mostrar en pricing page)
Route::get('/planes', function () {
    return response()->json(\App\Models\Plan::where('estado', true)->orderBy('orden')->get());
});

// Rutas protegidas (requieren token Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // ──────────────────────────────────────────────
    // Agente IA (endpoints para n8n Code Tool)
    // ──────────────────────────────────────────────
    Route::prefix('agente')->group(function () {
        Route::get('/farmacias-turno', [\App\Http\Controllers\Api\AgenteController::class, 'farmaciasTurno']);
        Route::get('/buscar-locales', [\App\Http\Controllers\Api\AgenteController::class, 'buscarLocales']);
        Route::get('/buscar-productos', [\App\Http\Controllers\Api\AgenteController::class, 'buscarProductos']);
        Route::get('/anuncios', [\App\Http\Controllers\Api\AgenteController::class, 'anuncios']);
        Route::get('/categorias', [\App\Http\Controllers\Api\AgenteController::class, 'categorias']);
        Route::get('/local/{slug}', [\App\Http\Controllers\Api\AgenteController::class, 'showLocal']);
    });

    // ──────────────────────────────────────────────
    // Súper Administración Global
    // ──────────────────────────────────────────────
    Route::prefix('superadmin')->group(function () {
        Route::get('/locales', [SuperadminController::class, 'getLocales']);
        Route::post('/locales', [SuperadminController::class, 'storeTenant']);
        Route::post('/locales/public', [SuperadminController::class, 'storePublicLocal']);
        Route::put('/locales/{id}/transfer', [SuperadminController::class, 'transferLocal']);
        Route::delete('/locales/{id}', [SuperadminController::class, 'deleteLocal']);

        Route::post('/locales/{id}/anuncios', [SuperadminController::class, 'storeAnuncio']);
        Route::get('/anuncios', [SuperadminController::class, 'getAnuncios']);
        Route::post('/anuncios', [SuperadminController::class, 'storeGlobalAnuncio']);
        Route::put('/anuncios/{id}', [SuperadminController::class, 'updateAnuncio']);
        Route::delete('/anuncios/{id}', [SuperadminController::class, 'deleteAnuncio']);
        
        Route::get('/dashboard', [SuperadminController::class, 'dashboard']);
        Route::put('/locales/{id}', [SuperadminController::class, 'updateLocal']);
        Route::get('/usuarios', [SuperadminController::class, 'getUsuarios']);
        Route::put('/usuarios/{id}', [SuperadminController::class, 'updateUsuario']);
        Route::get('/categorias', [SuperadminController::class, 'getCategorias']);
        Route::post('/categorias', [SuperadminController::class, 'storeCategoria']);
        Route::put('/categorias/{id}', [SuperadminController::class, 'updateCategoria']);
        Route::delete('/categorias/{id}', [SuperadminController::class, 'deleteCategoria']);
        
        Route::post('/categorias/{id}/subcategorias', [SuperadminController::class, 'storeSubcategoria']);
        Route::delete('/subcategorias/{id}', [SuperadminController::class, 'deleteSubcategoria']);
        
        Route::get('/planes', [SuperadminController::class, 'getPlanes']);
        Route::post('/planes', [SuperadminController::class, 'storePlan']);
        Route::put('/planes/{id}', [SuperadminController::class, 'updatePlan']);
        Route::delete('/planes/{id}', [SuperadminController::class, 'deletePlan']);
    });

    // Local Config (Store Owner)
    Route::prefix('local')->group(function () {
        Route::post('/crear', [LocalConfigController::class, 'crearLocal']);
        Route::get('/perfil', [LocalConfigController::class, 'getPerfil']);
        Route::put('/perfil', [LocalConfigController::class, 'updatePerfil']);
        Route::post('/perfil/logo', [LocalConfigController::class, 'uploadLogo']);
        Route::post('/perfil/portada', [LocalConfigController::class, 'uploadPortada']);
    });

    // ──────────────────────────────────────────────
    // POS Vendedor (requiere plan con POS)
    // ──────────────────────────────────────────────

    // Artículos / Stock — accesible para todos (pero limitado por max_productos)
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

    // Ventas (requiere POS)
    Route::middleware('plan.feature:pos')->group(function () {
        Route::get('/ventas/dashboard', [VentaController::class, 'dashboard']);
        Route::get('/ventas', [VentaController::class, 'index']);
        Route::post('/ventas', [VentaController::class, 'store']);
    });

    // Pedidos — accesible para todos los planes (recibir pedidos es free)
    Route::get('/pedidos', [PedidoController::class, 'index']);
    Route::post('/pedidos', [PedidoController::class, 'store']);
    Route::patch('/pedidos/{id}', [PedidoController::class, 'update']);
    Route::patch('/pedidos/{id}/estado', [PedidoController::class, 'updateEstado']);
    Route::post('/pedidos/{id}/entregar', [PedidoController::class, 'entregar']);
    Route::delete('/pedidos/{id}', [PedidoController::class, 'destroy']);

    // Clientes POS (requiere plan con clientes)
    Route::middleware('plan.feature:clientes')->group(function () {
        Route::get('/clientes-pos', [ClienteController::class, 'index']);
        Route::post('/clientes-pos', [ClienteController::class, 'store']);
        Route::put('/clientes-pos/{id}', [ClienteController::class, 'update']);
        Route::post('/clientes-pos/{id}/transacciones', [ClienteController::class, 'createTransaction']);
        Route::post('/clientes-pos/{id}/pago', [ClienteController::class, 'registrarPago']);
    });

    // Caja (requiere plan con caja)
    Route::middleware('plan.feature:caja')->group(function () {
        Route::get('/caja', [CajaController::class, 'index']);
        Route::post('/caja', [CajaController::class, 'store']);
    });

    // ──────────────────────────────────────────────
    // Financiera (requiere plan con créditos)
    // ──────────────────────────────────────────────
    Route::middleware('plan.feature:creditos')->group(function () {
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
    });

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
