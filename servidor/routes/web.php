<?php

use App\Http\Controllers\Web\PaginasPublicasController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/privacidad', [PaginasPublicasController::class, 'privacidad']);
Route::get('/borrar-cuenta', [PaginasPublicasController::class, 'borrarCuentaForm']);
Route::post('/borrar-cuenta/codigo', [PaginasPublicasController::class, 'enviarCodigoWeb']);
Route::post('/borrar-cuenta/confirmar', [PaginasPublicasController::class, 'confirmarBorradoWeb']);

