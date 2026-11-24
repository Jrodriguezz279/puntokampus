<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MiProductoController;
use App\Http\Controllers\Api\MisPedidosController;
use App\Http\Controllers\Api\MiTiendaController;
use App\Http\Controllers\Api\PedidoController;
use App\Http\Controllers\Api\PremiumController;
use App\Http\Controllers\Api\ReporteController;
use App\Http\Controllers\Api\TiendaPedidosController;
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
    Route::get('/healthcheck', function () {
        return response()->json([
            'estado' => 'ok',
            'version' => '1.0',
            'entorno' => config('app.env'),
            'hora' => now()->toIso8601String(),
        ]);
    });

    // Rutas públicas de Autenticación (R1, R2, R3, R4)
    Route::post('/auth/entrar', [AuthController::class, 'entrar']);
    Route::post('/auth/login', [AuthController::class, 'entrar']);
    Route::post('/auth/codigo', [AuthController::class, 'solicitarCodigo']);
    Route::post('/auth/verificar', [AuthController::class, 'verificarCodigo']);
    Route::post('/auth/codigo-recuperacion', [AuthController::class, 'solicitarRecuperacion']);
    Route::post('/auth/recuperar', [AuthController::class, 'recuperarContrasena']);

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
        Route::post('/yo/codigo-borrado', [YoController::class, 'solicitarCodigoBorrado']);
        Route::delete('/yo', [YoController::class, 'borrarCuenta']);

        // Mi Tienda (R6, R10)
        Route::get('/mi-tienda', [MiTiendaController::class, 'show']);
        Route::post('/mi-tienda', [MiTiendaController::class, 'store']);
        Route::put('/mi-tienda', [MiTiendaController::class, 'update']);
        Route::post('/mi-tienda/toggle-pedidos', [MiTiendaController::class, 'togglePedidos']);
        Route::post('/mi-tienda/logo', [MiTiendaController::class, 'subirLogo']);
        Route::post('/mi-tienda/portada', [MiTiendaController::class, 'subirPortada']);
        Route::post('/mi-tienda/deuna-qr', [MiTiendaController::class, 'subirDeunaQr']);
        Route::delete('/mi-tienda/deuna-qr', [MiTiendaController::class, 'eliminarDeunaQr']);

        // Solicitudes Premium de Mi Tienda (H20, R21)
        Route::get('/mi-tienda/premium', [PremiumController::class, 'show']);
        Route::post('/mi-tienda/premium', [PremiumController::class, 'store']);

        // Productos de Mi Tienda (R7, H07, H08)
        Route::get('/mi-tienda/productos', [MiProductoController::class, 'index']);
        Route::post('/mi-tienda/productos', [MiProductoController::class, 'store']);
        Route::match(['put', 'post'], '/mi-tienda/productos/{id}', [MiProductoController::class, 'update']);
        Route::delete('/mi-tienda/productos/{id}', [MiProductoController::class, 'destroy']);
        Route::post('/mi-tienda/productos/{id}/fotos', [MiProductoController::class, 'agregarFoto']);
        Route::delete('/mi-tienda/productos/{id}/fotos/{fotoId}', [MiProductoController::class, 'eliminarFoto']);

        // Pedidos (H13, R11, R12, R14, R15)
        Route::post('/pedidos', [PedidoController::class, 'store']);
        Route::get('/pedidos/{codigo}/qr', [PedidoController::class, 'verQr']);

        // Calificaciones bidireccionales (Fase 3: Estrellas)
        Route::post('/mis-pedidos/{codigo}/calificacion', [\App\Http\Controllers\Api\CalificacionController::class, 'calificarTienda']);
        Route::post('/mi-tienda/pedidos/{codigo}/calificacion', [\App\Http\Controllers\Api\CalificacionController::class, 'calificarComprador']);

        // Mis Pedidos como Comprador (H14, H15, R16)
        Route::get('/mis-pedidos', [MisPedidosController::class, 'index']);
        Route::get('/mis-pedidos/{codigo}', [MisPedidosController::class, 'show']);
        Route::delete('/mis-pedidos/{codigo}', [MisPedidosController::class, 'cancelar']);

        // Pedidos de Mi Tienda como Vendedor (H16, H17, R17)
        Route::get('/mi-tienda/pedidos', [TiendaPedidosController::class, 'index']);
        Route::match(['put', 'post'], '/mi-tienda/pedidos/{codigo}/confirmar-entrega', [TiendaPedidosController::class, 'confirmarEntrega']);
        Route::match(['put', 'post'], '/mi-tienda/pedidos/{codigo}/entregado', [TiendaPedidosController::class, 'entregar']);
        Route::match(['put', 'post'], '/mi-tienda/pedidos/{codigo}/cancelar', [TiendaPedidosController::class, 'cancelar']);

        // Reportes de Tiendas (H19, R20)
        Route::post('/tiendas/{id}/reportes', [ReporteController::class, 'store']);
    });
});
