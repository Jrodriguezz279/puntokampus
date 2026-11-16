<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
     * POST /api/v1/auth/entrar (o /api/v1/auth/login)
     */
    public function entrar(Request $request): JsonResponse
    {
        $request->validate([
            'correo' => ['required', 'string', 'email'],
            'contrasena' => ['sometimes', 'string'],
            'password' => ['sometimes', 'string'],
            'dispositivo' => ['nullable', 'string', 'max:120'],
        ]);

        $password = $request->input('contrasena') ?? $request->input('password');
        if (empty($password)) {
            return response()->json([
                'message' => 'La contraseña es requerida.',
                'errors' => ['contrasena' => ['La contraseña es requerida.']],
            ], 422);
        }

        $resultado = $this->authService->loginConContrasena(
            $request->input('correo'),
            $password,
            $request->input('dispositivo')
        );

        return response()->json([
            'token' => $resultado['token'],
            'usuario' => new UsuarioResource($resultado['usuario']),
            'es_nuevo' => false,
        ]);
    }

    /**
     * POST /api/v1/auth/codigo
     */
    public function solicitarCodigo(Request $request): Response
    {
        $request->validate([
            'correo' => ['required', 'string', 'email'],
        ]);

        $this->authService->solicitarCodigo($request->input('correo'), 'acceso');
        return response()->noContent();
    }

    /**
     * POST /api/v1/auth/verificar
     */
    public function verificarCodigo(Request $request): JsonResponse
    {
        $request->validate([
            'correo' => ['required', 'string', 'email'],
            'codigo' => ['required', 'string'],
            'contrasena' => ['nullable', 'string', 'min:6'],
            'password' => ['nullable', 'string', 'min:6'],
            'dispositivo' => ['nullable', 'string', 'max:120'],
        ]);

        $password = $request->input('contrasena') ?? $request->input('password');

        $resultado = $this->authService->verificarCodigo(
            $request->input('correo'),
            $request->input('codigo'),
            $request->input('dispositivo'),
            $password,
            'acceso'
        );

        return response()->json([
            'token' => $resultado['token'],
            'usuario' => new UsuarioResource($resultado['usuario']),
            'es_nuevo' => $resultado['es_nuevo'],
        ]);
    }

    /**
     * POST /api/v1/auth/codigo-recuperacion
     */
    public function solicitarRecuperacion(Request $request): Response
    {
        $request->validate([
            'correo' => ['required', 'string', 'email'],
        ]);

        $this->authService->solicitarCodigoRecuperacion($request->input('correo'));
        return response()->noContent();
    }

    /**
     * POST /api/v1/auth/recuperar
     */
    public function recuperarContrasena(Request $request): Response
    {
        $request->validate([
            'correo' => ['required', 'string', 'email'],
            'codigo' => ['required', 'string'],
            'contrasena' => ['sometimes', 'string', 'min:6'],
            'password' => ['sometimes', 'string', 'min:6'],
        ]);

        $password = $request->input('contrasena') ?? $request->input('password');
        if (empty($password)) {
            abort(422, 'La nueva contraseña es requerida.');
        }

        $this->authService->recuperarContrasena(
            $request->input('correo'),
            $request->input('codigo'),
            $password
        );

        return response()->noContent();
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
