<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GuardarProductoRequest;
use App\Http\Requests\SubirFotoRequest;
use App\Http\Resources\ProductoFotoResource;
use App\Http\Resources\ProductoResource;
use App\Models\Producto;
use App\Models\ProductoFoto;
use App\Services\ImagenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MiProductoController extends Controller
{
    public function __construct(protected ImagenService $imagenService)
    {
    }

    protected function obtenerTienda(Request $request)
    {
        $tienda = $request->user()->tienda;
        if (!$tienda) {
            abort(404, 'No tienes una tienda creada.');
        }
        return $tienda;
    }

    /**
     * GET /api/v1/mi-tienda/productos
     */
    public function index(Request $request): JsonResponse
    {
        $tienda = $this->obtenerTienda($request);
        $productos = $tienda->productos()
            ->whereNull('eliminado_en')
            ->with('fotos')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'productos' => ProductoResource::collection($productos),
        ]);
    }

    /**
     * POST /api/v1/mi-tienda/productos
     */
    public function store(GuardarProductoRequest $request): JsonResponse
    {
        $tienda = $this->obtenerTienda($request);
        $datos = $request->validated();

        $descuentoActivo = false;
        if (isset($datos['descuento_activo'])) {
            $descuentoActivo = (bool) $datos['descuento_activo'];
        } elseif (isset($datos['precio_descuento']) && $datos['precio_descuento'] > 0 && $datos['precio_descuento'] < $datos['precio']) {
            $descuentoActivo = true;
        }

        $producto = $tienda->productos()->create([
            'nombre' => $datos['nombre'],
            'descripcion' => $datos['descripcion'] ?? null,
            'precio' => $datos['precio'],
            'precio_descuento' => $datos['precio_descuento'] ?? null,
            'descuento_activo' => $descuentoActivo,
            'disponible' => $datos['disponible'] ?? true,
        ]);

        if ($request->hasFile('foto')) {
            $url = $this->imagenService->procesarYGuardar($request->file('foto'), 'productos');
            $producto->fotos()->create(['url' => $url, 'orden' => 1]);
        }

        $producto->load('fotos');

        return response()->json([
            'producto' => new ProductoResource($producto),
        ], 201);
    }

    /**
     * PUT /api/v1/mi-tienda/productos/{id}
     */
    public function update(GuardarProductoRequest $request, int $id): JsonResponse
    {
        $tienda = $this->obtenerTienda($request);
        $producto = $tienda->productos()->whereNull('eliminado_en')->findOrFail($id);

        $datos = $request->validated();
        if (array_key_exists('precio_descuento', $datos)) {
            $precioBase = $datos['precio'] ?? $producto->precio;
            $datos['descuento_activo'] = ($datos['precio_descuento'] !== null && $datos['precio_descuento'] > 0 && $datos['precio_descuento'] < $precioBase);
        }

        $producto->update($datos);

        if ($request->hasFile('foto')) {
            $url = $this->imagenService->procesarYGuardar($request->file('foto'), 'productos');
            $primeraFoto = $producto->fotos()->orderBy('orden')->first();
            if ($primeraFoto) {
                $primeraFoto->update(['url' => $url]);
            } else {
                $producto->fotos()->create(['url' => $url, 'orden' => 1]);
            }
        }

        $producto->load('fotos');

        return response()->json([
            'producto' => new ProductoResource($producto),
        ]);
    }

    /**
     * DELETE /api/v1/mi-tienda/productos/{id} (Borrado suave)
     */
    public function destroy(Request $request, int $id): Response
    {
        $tienda = $this->obtenerTienda($request);
        $producto = $tienda->productos()->whereNull('eliminado_en')->findOrFail($id);

        $producto->update(['eliminado_en' => now()]);

        return response()->noContent();
    }

    /**
     * POST /api/v1/mi-tienda/productos/{id}/fotos (R7: Máx 3 fotos)
     */
    public function agregarFoto(SubirFotoRequest $request, int $id): JsonResponse
    {
        $tienda = $this->obtenerTienda($request);
        $producto = $tienda->productos()->whereNull('eliminado_en')->findOrFail($id);

        $url = $this->imagenService->procesarYGuardar($request->file('foto'), 'productos');

        $primeraFoto = $producto->fotos()->orderBy('orden')->first();
        if ($request->boolean('reemplazar') && $primeraFoto) {
            $primeraFoto->update(['url' => $url]);
            $foto = $primeraFoto;
        } elseif ($producto->fotos()->count() >= 3) {
            return response()->json([
                'message' => 'Cada producto puede tener hasta 3 fotos.',
                'errors' => ['foto' => ['Cada producto puede tener hasta 3 fotos.']],
            ], 422);
        } else {
            $orden = ($producto->fotos()->max('orden') ?? 0) + 1;
            $foto = $producto->fotos()->create([
                'url' => $url,
                'orden' => $orden,
            ]);
        }

        return response()->json([
            'foto' => new ProductoFotoResource($foto),
        ], 201);
    }

    /**
     * DELETE /api/v1/mi-tienda/productos/{id}/fotos/{fotoId}
     */
    public function eliminarFoto(Request $request, int $id, int $fotoId): Response
    {
        $tienda = $this->obtenerTienda($request);
        $producto = $tienda->productos()->whereNull('eliminado_en')->findOrFail($id);
        $foto = $producto->fotos()->findOrFail($fotoId);

        $foto->delete();

        return response()->noContent();
    }
}

