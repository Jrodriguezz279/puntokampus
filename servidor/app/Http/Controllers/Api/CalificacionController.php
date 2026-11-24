<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Calificacion;
use App\Models\Pedido;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalificacionController extends Controller
{
    /**
     * POST /api/v1/mis-pedidos/{codigo}/calificacion
     * Comprador califica a la tienda
     */
    public function calificarTienda(Request $request, string $codigo): JsonResponse
    {
        $request->validate([
            'estrellas' => ['required', 'integer', 'min:1', 'max:5'],
        ]);

        $usuario = $request->user();
        $pedido = Pedido::where('codigo', $codigo)->first();

        if (!$pedido || (int) $pedido->comprador_id !== (int) $usuario->id) {
            abort(404, 'Pedido no encontrado.');
        }

        if ($pedido->estado !== 'entregado' || !$pedido->enPlazoCalificacion() || !$pedido->puedeCalificarComprador()) {
            return response()->json([
                'message' => 'Este pedido ya no se puede calificar.',
            ], 422);
        }

        $calificacion = Calificacion::create([
            'pedido_id' => $pedido->id,
            'direccion' => 'a_tienda',
            'autor_id'  => $usuario->id,
            'estrellas' => (int) $request->input('estrellas'),
            'creada_en' => now(),
        ]);

        return response()->json([
            'message' => 'Gracias. Tu calificación se verá cuando la otra parte también califique o en 3 días.',
            'calificacion' => [
                'estrellas'  => $calificacion->estrellas,
                'direccion'  => $calificacion->direccion,
                'es_visible' => $pedido->calificacionesSonVisibles(),
            ],
        ], 201);
    }

    /**
     * POST /api/v1/mi-tienda/pedidos/{codigo}/calificacion
     * Vendedor califica al comprador
     */
    public function calificarComprador(Request $request, string $codigo): JsonResponse
    {
        $request->validate([
            'estrellas' => ['required', 'integer', 'min:1', 'max:5'],
        ]);

        $usuario = $request->user();
        $tienda = $usuario->tienda;

        if (!$tienda) {
            abort(404, 'Tienda no encontrada.');
        }

        $pedido = Pedido::where('codigo', $codigo)
            ->where('tienda_id', $tienda->id)
            ->first();

        if (!$pedido) {
            abort(404, 'Pedido no encontrado.');
        }

        if ($pedido->estado !== 'entregado' || !$pedido->enPlazoCalificacion() || !$pedido->puedeCalificarVendedor()) {
            return response()->json([
                'message' => 'Este pedido ya no se puede calificar.',
            ], 422);
        }

        $calificacion = Calificacion::create([
            'pedido_id' => $pedido->id,
            'direccion' => 'a_comprador',
            'autor_id'  => $usuario->id,
            'estrellas' => (int) $request->input('estrellas'),
            'creada_en' => now(),
        ]);

        return response()->json([
            'message' => 'Gracias. Tu calificación se verá cuando la otra parte también califique o en 3 días.',
            'calificacion' => [
                'estrellas'  => $calificacion->estrellas,
                'direccion'  => $calificacion->direccion,
                'es_visible' => $pedido->calificacionesSonVisibles(),
            ],
        ], 201);
    }
}
