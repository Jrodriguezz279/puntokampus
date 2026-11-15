<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoriaResource;
use App\Http\Resources\ProductoResource;
use App\Http\Resources\PuntoEntregaResource;
use App\Http\Resources\TiendaDetalleResource;
use App\Http\Resources\TiendaResource;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\PuntoEntrega;
use App\Models\Tienda;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VitrinaController extends Controller
{
    /**
     * GET /api/v1/categorias
     */
    public function categorias(): JsonResponse
    {
        $categorias = Categoria::where('activa', true)
            ->orderBy('orden')
            ->get();

        return response()->json([
            'categorias' => CategoriaResource::collection($categorias),
        ]);
    }

    /**
     * GET /api/v1/puntos-entrega
     */
    public function puntosEntrega(): JsonResponse
    {
        $puntos = PuntoEntrega::where('activa', true)
            ->orderBy('orden')
            ->get();

        $collection = PuntoEntregaResource::collection($puntos);

        return response()->json([
            'puntos' => $collection,
            'puntos_entrega' => $collection,
            'data' => $collection,
        ]);
    }

    /**
     * GET /api/v1/tiendas (R8: Solo activas; 20 por página; búsqueda sin tildes ni mayúsculas H10)
     */
    public function tiendas(Request $request): JsonResponse
    {
        $query = Tienda::where('estado', 'activa')
            ->with(['categoria1', 'categoria2', 'puntoEntrega', 'usuario']);

        // Filtro por categoría (categoría 1 o 2)
        if ($request->filled('categoria')) {
            $catId = (int) $request->input('categoria');
            $query->where(function ($q) use ($catId) {
                $q->where('categoria_1_id', $catId)
                  ->orWhere('categoria_2_id', $catId);
            });
        }

        // Búsqueda con unaccent + ILIKE
        if ($request->filled('buscar')) {
            $termino = trim($request->input('buscar'));
            $query->where(function ($q) use ($termino) {
                $q->whereRaw("unaccent(lower(nombre)) ILIKE unaccent(lower(?))", ["%{$termino}%"])
                  ->orWhereRaw("unaccent(lower(coalesce(descripcion, ''))) ILIKE unaccent(lower(?))", ["%{$termino}%"]);
            });
        }

        $tiendas = $query->orderByRaw("CASE WHEN plan = 'premium' THEN 0 ELSE 1 END")
            ->orderBy('id', 'desc')
            ->paginate(20);

        return response()->json([
            'tiendas' => TiendaResource::collection($tiendas->items()),
            'paginacion' => [
                'total' => $tiendas->total(),
                'pagina_actual' => $tiendas->currentPage(),
                'ultima_pagina' => $tiendas->lastPage(),
                'por_pagina' => $tiendas->perPage(),
            ],
        ]);
    }

    /**
     * GET /api/v1/tiendas/{slug} (H12: Enlace público)
     */
    public function verTienda(string $slug): JsonResponse
    {
        $tienda = Tienda::where('slug', $slug)
            ->with(['categoria1', 'categoria2', 'puntoEntrega', 'usuario', 'productos.fotos'])
            ->first();

        // R8: Si no existe o está dada de baja
        if (!$tienda || $tienda->estado !== 'activa') {
            return response()->json([
                'message' => 'Esta tienda no está disponible.',
            ], 404);
        }

        return response()->json([
            'tienda' => new TiendaDetalleResource($tienda),
        ]);
    }

    /**
     * GET /api/v1/productos (H10: Búsqueda sin tildes ni mayúsculas en productos de tiendas activas)
     */
    public function productos(Request $request): JsonResponse
    {
        $query = Producto::whereNull('eliminado_en')
            ->whereHas('tienda', function ($q) {
                $q->where('estado', 'activa');
            })
            ->with(['fotos', 'tienda']);

        // Filtro por categoría (categoría 1 o 2 de la tienda)
        if ($request->filled('categoria')) {
            $catId = (int) $request->input('categoria');
            $query->whereHas('tienda', function ($q) use ($catId) {
                $q->where('categoria_1_id', $catId)
                  ->orWhere('categoria_2_id', $catId);
            });
        }

        // Búsqueda con unaccent + ILIKE
        if ($request->filled('buscar')) {
            $termino = trim($request->input('buscar'));
            $query->where(function ($q) use ($termino) {
                $q->whereRaw("unaccent(lower(nombre)) ILIKE unaccent(lower(?))", ["%{$termino}%"])
                  ->orWhereRaw("unaccent(lower(coalesce(descripcion, ''))) ILIKE unaccent(lower(?))", ["%{$termino}%"]);
            });
        }

        $productos = $query->orderBy('id', 'desc')->paginate(20);

        return response()->json([
            'productos' => ProductoResource::collection($productos->items()),
            'paginacion' => [
                'total' => $productos->total(),
                'pagina_actual' => $productos->currentPage(),
                'ultima_pagina' => $productos->lastPage(),
                'por_pagina' => $productos->perPage(),
            ],
        ]);
    }
}
