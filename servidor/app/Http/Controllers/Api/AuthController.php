<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SolicitarCodigoRequest;
use App\Http\Requests\VerificarCodigoRequest;
use App\Http\Resources\UsuarioResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    public function __construct(protected AuthService $authService)
    {
    }

    /**
     * POST /api/v1/auth/codigo
     */
    public function solicitarCodigo(SolicitarCodigoRequest $request): Response
    {
        $this->authService->solicitarCodigo($request->input('correo'));
        return response()->noContent();
    }

    /**
     * POST /api/v1/auth/verificar
     */
    public function verificarCodigo(VerificarCodigoRequest $request): JsonResponse
    {
        $resultado = $this->authService->verificarCodigo(
            $request->input('correo'),
            $request->input('codigo'),
            $request->input('dispositivo')
        );

        return response()->json([
            'token' => $resultado['token'],
            'usuario' => new UsuarioResource($resultado['usuario']),
            'es_nuevo' => $resultado['es_nuevo'],
        ]);
    }

    /**
     * POST /api/v1/auth/salir
     */
    public function salir(Request $request): Response
    {
        $request->user()->currentAccessToken()->delete();
        return response()->noContent();
    }
}

