<?php

use App\Http\Controllers\Web\DemoController;
use App\Http\Controllers\Web\PaginasPublicasController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/privacidad', [PaginasPublicasController::class, 'privacidad']);
Route::get('/borrar-cuenta', [PaginasPublicasController::class, 'borrarCuentaForm']);
Route::post('/borrar-cuenta/codigo', [PaginasPublicasController::class, 'enviarCodigoWeb']);
Route::post('/borrar-cuenta/confirmar', [PaginasPublicasController::class, 'confirmarBorradoWeb']);

// Vista interactiva del Estudiante Vendedor (Demo)
Route::get('/demo', [DemoController::class, 'index']);
Route::post('/demo/producto', [DemoController::class, 'agregarProducto']);
Route::post('/demo/pedidos/{codigo}/entregado', [DemoController::class, 'entregarPedido']);
Route::post('/demo/simular-pedido', [DemoController::class, 'simularPedido']);

