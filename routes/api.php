<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SocioController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


// Auth público (sin token)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Rutas protegidas (requieren token Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Tus endpoints de negocio aquí...
    // Socios
    Route::get('/socios', [SocioController::class, 'index']);
    Route::post('/socios', [SocioController::class, 'store']);
    Route::get('/socios/{id}', [SocioController::class, 'show']);
    Route::put('/socios/{id}', [SocioController::class, 'update']);
    Route::delete('/socios/{id}', [SocioController::class, 'destroy']);
    Route::post('/socios/{id}/create-user', [SocioController::class, 'createUser']);
    Route::get('/perfil', [SocioController::class, 'getProfile']);

    // Servicios (Planes, Clases)
    Route::apiResource('servicios', \App\Http\Controllers\Api\ServicioController::class);

    // Clases (Horarios/Templates)
    Route::apiResource('clases', \App\Http\Controllers\Api\ClaseGymController::class);

    // Reservas y Disponibilidad
    Route::get('/clases-disponibles', [\App\Http\Controllers\Api\ReservaGymController::class, 'getAvailableClasses']);
    Route::get('/asistencias', [\App\Http\Controllers\Api\AsistenciaGymController::class, 'index']);
    Route::post('/asistencias', [\App\Http\Controllers\Api\AsistenciaGymController::class, 'store']);
    Route::apiResource('reservas', \App\Http\Controllers\Api\ReservaGymController::class);

    // Membresías
    Route::apiResource('membresias', \App\Http\Controllers\Api\MembresiaController::class);
    Route::apiResource('pagos-gym', \App\Http\Controllers\Api\PagoGymController::class);
});
