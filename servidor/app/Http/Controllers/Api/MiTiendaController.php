<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GuardarTiendaRequest;
use App\Http\Requests\SubirFotoRequest;
use App\Http\Resources\TiendaResource;
use App\Models\Tienda;
use App\Services\ImagenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MiTiendaController extends Controller
{
    public function __construct(protected ImagenService $imagenService)
    {
    }

    /**
     * GET /api/v1/mi-tienda
     */
    public function show(Request $request): JsonResponse
    {
        $tienda = $request->user()->tienda;
        if (!$tienda) {
            abort(404, 'No tienes una tienda creada todavía.');
        }

        $tienda->load(['categoria1', 'categoria2', 'puntoEntrega', 'usuario']);

        return response()->json([
            'tienda' => new TiendaResource($tienda),
        ]);
    }

    /**
     * POST /api/v1/mi-tienda
     */
    public function store(GuardarTiendaRequest $request): JsonResponse
    {
        $usuario = $request->user();

        // R6: Máximo una tienda por usuario
        if ($usuario->tienda) {
            abort(422, 'Ya tienes una tienda creada.');
        }

        $datos = $request->validated();

        // Actualizar datos del vendedor si vinieron
        $actualizacionesUsuario = [];
        foreach (['codigo_estudiante', 'facultad', 'carrera', 'semestre'] as $campo) {
            if (!empty($datos[$campo])) {
                $actualizacionesUsuario[$campo] = $datos[$campo];
            }
        }

        if (!empty($datos['whatsapp'])) {
            // Formatear WhatsApp a formato internacional: 09XXXXXXXX -> 5939XXXXXXXX
            $numeroLimpio = preg_replace('/\D/', '', $datos['whatsapp']);
            if (str_starts_with($numeroLimpio, '09')) {
                $numeroLimpio = '593' . substr($numeroLimpio, 1);
            } elseif (str_starts_with($numeroLimpio, '9') && strlen($numeroLimpio) === 9) {
                $numeroLimpio = '593' . $numeroLimpio;
            }
            $actualizacionesUsuario['whatsapp'] = $numeroLimpio;
        }

        if (!empty($actualizacionesUsuario)) {
            $usuario->update($actualizacionesUsuario);
        }

        // Generar slug único
        $slugBase = Str::slug($datos['nombre']);
        $slug = $slugBase;
        $contador = 1;
        while (Tienda::where('slug', $slug)->exists()) {
            $slug = "{$slugBase}-{$contador}";
            $contador++;
        }

        $tienda = Tienda::create([
            'usuario_id' => $usuario->id,
            'nombre' => $datos['nombre'],
            'slug' => $slug,
            'descripcion' => $datos['descripcion'] ?? null,
            'categoria_1_id' => $datos['categoria_1_id'],
            'categoria_2_id' => $datos['categoria_2_id'] ?? null,
            'horario' => $datos['horario'] ?? null,
            'punto_entrega_id' => $datos['punto_entrega_id'] ?? null,
            'plan' => 'gratis',
            'estado' => 'activa',
        ]);

        $tienda->load(['categoria1', 'categoria2', 'puntoEntrega', 'usuario']);

        return response()->json([
            'mensaje' => '¡Tu tienda ya está en la vitrina!',
            'tienda' => new TiendaResource($tienda),
        ], 201);
    }

    /**
     * PUT /api/v1/mi-tienda
     */
    public function update(GuardarTiendaRequest $request): JsonResponse
    {
        $usuario = $request->user();
        $tienda = $usuario->tienda;
        if (!$tienda) {
            abort(404, 'No tienes una tienda creada.');
        }

        $datos = $request->validated();

        // Actualizar datos del vendedor si se enviaron
        $actualizacionesUsuario = [];
        foreach (['codigo_estudiante', 'facultad', 'carrera', 'semestre'] as $campo) {
            if (!empty($datos[$campo])) {
                $actualizacionesUsuario[$campo] = $datos[$campo];
            }
        }
        if (!empty($datos['whatsapp'])) {
            $numeroLimpio = preg_replace('/\D/', '', $datos['whatsapp']);
            if (str_starts_with($numeroLimpio, '09')) {
                $numeroLimpio = '593' . substr($numeroLimpio, 1);
            } elseif (str_starts_with($numeroLimpio, '9') && strlen($numeroLimpio) === 9) {
                $numeroLimpio = '593' . $numeroLimpio;
            }
            $actualizacionesUsuario['whatsapp'] = $numeroLimpio;
        }
        if (!empty($actualizacionesUsuario)) {
            $usuario->update($actualizacionesUsuario);
        }

        // Actualizar tienda
        $tienda->update([
            'nombre' => $datos['nombre'],
            'descripcion' => $datos['descripcion'] ?? null,
            'categoria_1_id' => $datos['categoria_1_id'],
            'categoria_2_id' => $datos['categoria_2_id'] ?? null,
            'horario' => $datos['horario'] ?? null,
            'punto_entrega_id' => $datos['punto_entrega_id'] ?? null,
        ]);

        $tienda->load(['categoria1', 'categoria2', 'puntoEntrega', 'usuario']);

        return response()->json([
            'tienda' => new TiendaResource($tienda),
        ]);
    }

    /**
     * POST /api/v1/mi-tienda/logo
     */
    public function subirLogo(SubirFotoRequest $request): JsonResponse
    {
        $tienda = $request->user()->tienda;
        if (!$tienda) {
            abort(404, 'No tienes una tienda creada.');
        }

        $url = $this->imagenService->procesarYGuardar($request->file('foto'), 'logos');
        $tienda->update(['logo_url' => $url]);

        return response()->json([
            'logo_url' => $url,
        ]);
    }

    /**
     * POST /api/v1/mi-tienda/portada (R10: Solo plan premium)
     */
    public function subirPortada(SubirFotoRequest $request): JsonResponse
    {
        $tienda = $request->user()->tienda;
        if (!$tienda) {
            abort(404, 'No tienes una tienda creada.');
        }

        if ($tienda->plan !== 'premium') {
            abort(403, 'La portada personalizada es un beneficio exclusivo para tiendas con plan Premium (Sacó 10).');
        }

        $url = $this->imagenService->procesarYGuardar($request->file('foto'), 'portadas');
        $tienda->update(['portada_url' => $url]);

        return response()->json([
            'portada_url' => $url,
        ]);
    }
}

