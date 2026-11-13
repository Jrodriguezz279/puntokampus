<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActualizarPerfilRequest;
use App\Http\Resources\UsuarioResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class YoController extends Controller
{
    /**
     * GET /api/v1/yo
     */
    public function show(Request $request): JsonResponse
    {
        $usuario = $request->user()->load('tienda');

        $resumenTienda = null;
        if ($usuario->tienda) {
            $resumenTienda = [
                'id' => $usuario->tienda->id,
                'nombre' => $usuario->tienda->nombre,
                'slug' => $usuario->tienda->slug,
                'plan' => $usuario->tienda->plan,
                'estado' => $usuario->tienda->estado,
                'logo_url' => $usuario->tienda->logo_url,
            ];
        }

        return response()->json([
            'usuario' => new UsuarioResource($usuario),
            'tienda' => $resumenTienda,
        ]);
    }

    /**
     * PUT /api/v1/yo
     */
    public function update(ActualizarPerfilRequest $request): JsonResponse
    {
        $usuario = $request->user();
        $datos = $request->validated();

        if ($request->boolean('acepto_privacidad') && !$usuario->acepto_privacidad_en) {
            $datos['acepto_privacidad_en'] = now();
        }

        unset($datos['acepto_privacidad']);

        $usuario->update($datos);

        return response()->json([
            'usuario' => new UsuarioResource($usuario->fresh()),
        ]);
    }
}

