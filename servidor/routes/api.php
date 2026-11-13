<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/salud', function () {
        return response()->json([
            'estado' => 'ok',
            'version' => '1.0',
            'entorno' => config('app.env'),
            'hora' => now()->toIso8601String(),
        ]);
    });
});
