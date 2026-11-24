<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PedidoResource;
use App\Models\Pedido;
use Illuminate\Http\Request;

class MisPedidosController extends Controller
{
    public function index(Request $request)
    {
        $comprador = $request->user();

        $query = Pedido::with(['tienda.usuario', 'puntoEntrega', 'items'])
            ->where('comprador_id', $comprador->id);

        if ($request->filled('estado') && in_array($request->query('estado'), ['pendiente', 'entregado', 'cancelado', 'vencido'])) {
            $query->where('estado', $request->query('estado'));
        }

        $pedidos = $query->orderBy('creado_en', 'desc')
            ->paginate(20);

        return PedidoResource::collection($pedidos);
    }

    public function show(Request $request, string $codigo)
    {
        $comprador = $request->user();

        $pedido = Pedido::with(['tienda.usuario', 'puntoEntrega', 'items'])
            ->where('codigo', $codigo)
            ->where('comprador_id', $comprador->id)
            ->first();

        if (!$pedido) {
            return response()->json([
                'message' => 'Pedido no encontrado.',
            ], 404);
        }

        return new PedidoResource($pedido);
    }

    public function cancelar(Request $request, string $codigo)
    {
        $comprador = $request->user();

        $pedido = Pedido::with(['tienda.usuario', 'puntoEntrega', 'items'])
            ->where('codigo', $codigo)
            ->where('comprador_id', $comprador->id)
            ->first();

        if (!$pedido) {
            return response()->json([
                'message' => 'Pedido no encontrado.',
            ], 404);
        }

        // R16: Solo si estado es pendiente se puede cancelar
        if ($pedido->estado !== 'pendiente') {
            return response()->json([
                'message' => "No se puede cancelar un pedido en estado '{$pedido->estado}'.",
            ], 422);
        }

        $pedido->update([
            'estado'             => 'cancelado',
            'cancelado_por'      => 'comprador',
            'estado_cambiado_en' => now(),
        ]);

        return new PedidoResource($pedido);
    }
}

