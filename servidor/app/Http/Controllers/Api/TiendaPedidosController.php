<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PedidoResource;
use App\Models\Pedido;
use Illuminate\Http\Request;

class TiendaPedidosController extends Controller
{
    public function index(Request $request)
    {
        $tienda = $request->user()->tienda;

        if (!$tienda) {
            return response()->json([
                'message' => 'No tienes una tienda registrada.',
            ], 404);
        }

        $query = Pedido::with(['comprador', 'puntoEntrega', 'items', 'tienda.usuario'])
            ->where('tienda_id', $tienda->id);

        if ($request->filled('estado')) {
            $estado = $request->input('estado');
            if (in_array($estado, ['pendiente', 'entregado', 'cancelado', 'vencido'])) {
                $query->where('estado', $estado);
            }
        }

        $pedidos = $query->orderBy('creado_en', 'desc')->paginate(15);

        return PedidoResource::collection($pedidos);
    }

    public function entregar(Request $request, string $codigo)
    {
        $tienda = $request->user()->tienda;

        if (!$tienda) {
            return response()->json([
                'message' => 'No tienes una tienda registrada.',
            ], 404);
        }

        $pedido = Pedido::where('codigo', $codigo)
            ->where('tienda_id', $tienda->id)
            ->first();

        if (!$pedido) {
            return response()->json([
                'message' => 'Pedido no encontrado.',
            ], 404);
        }

        // R17: Solo si el estado es pendiente puede marcarse como entregado
        if ($pedido->estado !== 'pendiente') {
            return response()->json([
                'message' => "No se puede marcar como entregado un pedido en estado '{$pedido->estado}'.",
            ], 422);
        }

        $pedido->update([
            'estado'             => 'entregado',
            'estado_cambiado_en' => now(),
        ]);

        $pedido->load(['comprador', 'puntoEntrega', 'items', 'tienda.usuario']);

        return new PedidoResource($pedido);
    }

    public function cancelar(Request $request, string $codigo)
    {
        $tienda = $request->user()->tienda;

        if (!$tienda) {
            return response()->json([
                'message' => 'No tienes una tienda registrada.',
            ], 404);
        }

        $pedido = Pedido::where('codigo', $codigo)
            ->where('tienda_id', $tienda->id)
            ->first();

        if (!$pedido) {
            return response()->json([
                'message' => 'Pedido no encontrado.',
            ], 404);
        }

        // R17: Solo si el estado es pendiente puede cancelarse
        if ($pedido->estado !== 'pendiente') {
            return response()->json([
                'message' => "No se puede cancelar un pedido en estado '{$pedido->estado}'.",
            ], 422);
        }

        $pedido->update([
            'estado'             => 'cancelado',
            'cancelado_por'      => 'vendedor',
            'estado_cambiado_en' => now(),
        ]);

        $pedido->load(['comprador', 'puntoEntrega', 'items', 'tienda.usuario']);

        return new PedidoResource($pedido);
    }
}
