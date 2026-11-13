<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MiProductoController;
use App\Http\Controllers\Api\MiTiendaController;
use App\Http\Controllers\Api\VitrinaController;
use App\Http\Controllers\Api\YoController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Endpoint de Salud
    Route::get('/salud', function () {
        return response()->json([
            'estado' => 'ok',
            'version' => '1.0',
            'entorno' => config('app.env'),
            'hora' => now()->toIso8601String(),
        ]);
    });

    // Rutas públicas de Autenticación (R1, R2, R3)
    Route::post('/auth/codigo', [AuthController::class, 'solicitarCodigo']);
    Route::post('/auth/verificar', [AuthController::class, 'verificarCodigo']);

    // Rutas públicas de Vitrina (H09, H10, H11, H12)
    Route::get('/categorias', [VitrinaController::class, 'categorias']);
    Route::get('/puntos-entrega', [VitrinaController::class, 'puntosEntrega']);
    Route::get('/tiendas', [VitrinaController::class, 'tiendas']);
    Route::get('/tiendas/{slug}', [VitrinaController::class, 'verTienda']);
    Route::get('/productos', [VitrinaController::class, 'productos']);

    // Rutas protegidas (🔒 Sanctum)
    Route::middleware('auth:sanctum')->group(function () {
        // Cuenta
        Route::post('/auth/salir', [AuthController::class, 'salir']);
        Route::get('/yo', [YoController::class, 'show']);
        Route::put('/yo', [YoController::class, 'update']);

        // Mi Tienda (R6, R10)
        Route::get('/mi-tienda', [MiTiendaController::class, 'show']);
        Route::post('/mi-tienda', [MiTiendaController::class, 'store']);
        Route::put('/mi-tienda', [MiTiendaController::class, 'update']);
        Route::post('/mi-tienda/logo', [MiTiendaController::class, 'subirLogo']);
        Route::post('/mi-tienda/portada', [MiTiendaController::class, 'subirPortada']);

        // Productos de Mi Tienda (R7, H07, H08)
        Route::get('/mi-tienda/productos', [MiProductoController::class, 'index']);
        Route::post('/mi-tienda/productos', [MiProductoController::class, 'store']);
        Route::put('/mi-tienda/productos/{id}', [MiProductoController::class, 'update']);
        Route::delete('/mi-tienda/productos/{id}', [MiProductoController::class, 'destroy']);
        Route::post('/mi-tienda/productos/{id}/fotos', [MiProductoController::class, 'agregarFoto']);
        Route::delete('/mi-tienda/productos/{id}/fotos/{fotoId}', [MiProductoController::class, 'eliminarFoto']);
    });
});
