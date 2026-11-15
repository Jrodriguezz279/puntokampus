<?php

use App\Http\Controllers\Web\AppEstudianteController;
use App\Http\Controllers\Web\DemoController;
use App\Http\Controllers\Web\PaginasPublicasController;
use Illuminate\Support\Facades\Route;

// Redirigir la raíz al Portal Oficial de Estudiantes
Route::get('/', function () {
    return redirect('/app');
});

// Páginas Públicas Exigidas por Google Play y Políticas
Route::get('/privacidad', [PaginasPublicasController::class, 'privacidad']);
Route::get('/borrar-cuenta', [PaginasPublicasController::class, 'borrarCuentaForm']);
Route::post('/borrar-cuenta/codigo', [PaginasPublicasController::class, 'enviarCodigoWeb']);
Route::post('/borrar-cuenta/confirmar', [PaginasPublicasController::class, 'confirmarBorradoWeb']);

// ==============================================================================
// Web App Oficial de Estudiantes (Material 3 - Vendedores y Compradores)
// ==============================================================================
Route::prefix('app')->group(function () {
    // Rutas públicas (con o sin sesión)
    Route::get('/', [AppEstudianteController::class, 'vitrina']);
    Route::get('/tienda/{slug}', [AppEstudianteController::class, 'verTienda']);

    // Autenticación por Código UTB de 6 dígitos (sin contraseñas)
    Route::get('/login', [AppEstudianteController::class, 'loginView'])->name('login');
    Route::post('/login/codigo', [AppEstudianteController::class, 'solicitarCodigo']);
    Route::get('/login/verificar', [AppEstudianteController::class, 'verificarView']);
    Route::post('/login/verificar', [AppEstudianteController::class, 'verificarCodigo']);
    Route::get('/login-rapido/{tipo}', [AppEstudianteController::class, 'loginRapido']);

    // Rutas con Sesión Activa (Compradores y Vendedores)
    Route::middleware('auth')->group(function () {
        // Modo Comprador
        Route::get('/mis-pedidos', [AppEstudianteController::class, 'misPedidos']);
        Route::post('/pedidos', [AppEstudianteController::class, 'crearPedido']);
        Route::post('/tienda/{slug}/reportar', [AppEstudianteController::class, 'reportarTienda']);

        // Modo Vendedor ("Mi Tienda")
        Route::get('/mi-tienda', [AppEstudianteController::class, 'miTienda']);
        Route::post('/mi-tienda/crear', [AppEstudianteController::class, 'guardarTienda']);
        Route::post('/mi-tienda/logo', [AppEstudianteController::class, 'subirLogo']);
        Route::post('/mi-tienda/producto', [AppEstudianteController::class, 'guardarProducto']);
        Route::post('/mi-tienda/premium', [AppEstudianteController::class, 'solicitarPremium']);
        Route::post('/mi-tienda/pedidos/{codigo}/confirmar-entrega', [AppEstudianteController::class, 'confirmarEntrega']);
        Route::post('/mi-tienda/pedidos/{codigo}/entregado', [AppEstudianteController::class, 'marcarEntregado']);
        Route::post('/mi-tienda/pedidos/{codigo}/cancelar', [AppEstudianteController::class, 'marcarCancelado']);

        // Perfil y Sesión
        Route::get('/cuenta', [AppEstudianteController::class, 'cuenta']);
        Route::post('/cuenta/salir', [AppEstudianteController::class, 'salir']);
    });
});

// Atajo para cerrar sesión de estudiante e ir al login de Administrador
Route::get('/admin-login', function () {
    \Illuminate\Support\Facades\Auth::logout();
    return redirect('/admin/login');
});

