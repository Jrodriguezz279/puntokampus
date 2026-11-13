<?php

use App\Http\Controllers\Api\AuthController;
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

    // Rutas protegidas (🔒 Sanctum)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/salir', [AuthController::class, 'salir']);
        Route::get('/yo', [YoController::class, 'show']);
        Route::put('/yo', [YoController::class, 'update']);
    });
});
