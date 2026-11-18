<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CrearPedidoRequest;
use App\Http\Resources\PedidoResource;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PedidoController extends Controller
{
    public function store(CrearPedidoRequest $request)
    {
        $comprador = $request->user();
        $itemsInput = $request->input('items');
        $productoIds = collect($itemsInput)->pluck('producto_id')->all();

        // 1. Obtener productos solicitados
        $productos = Producto::with('tienda.usuario')
            ->whereIn('id', $productoIds)
            ->get();

        // 2. Verificar que todos los productos existen, estén disponibles y no eliminados
        if ($productos->count() !== count(array_unique($productoIds))) {
            return response()->json([
                'message' => 'Uno o más productos no existen.',
            ], 422);
        }

        foreach ($productos as $producto) {
            if (!$producto->disponible || $producto->eliminado_en !== null) {
                return response()->json([
                    'message' => "El producto '{$producto->nombre}' no está disponible actualmente.",
                ], 422);
            }
        }

        // 3. R11: Verificar que todos pertenezcan a la misma tienda
        $tiendaIds = $productos->pluck('tienda_id')->unique();
        if ($tiendaIds->count() > 1) {
            return response()->json([
                'message' => 'Todos los productos del pedido deben pertenecer a la misma tienda.',
            ], 422);
        }

        $tienda = $productos->first()->tienda;

        if ($tienda->estado !== 'activa') {
            return response()->json([
                'message' => 'La tienda seleccionada no se encuentra activa.',
            ], 422);
        }

        // Validación: Interruptor "No estoy recibiendo pedidos"
        if (isset($tienda->recibiendo_pedidos) && !$tienda->recibiendo_pedidos) {
            return response()->json([
                'message' => 'Esta tienda ha pausado la recepción de pedidos temporalmente ("No estoy recibiendo pedidos").',
            ], 422);
        }

        // Validación: Horario por días y franja (los pedidos solo se aceptan dentro del horario o desde la siguiente apertura)
        if (!empty($tienda->hora_apertura) && !empty($tienda->hora_cierre)) {
            $horaEntrega = \Carbon\Carbon::parse($request->input('hora_entrega'))->timezone('America/Guayaquil');
            $horaEntregaStr = $horaEntrega->format('H:i');
            if ($horaEntregaStr < $tienda->hora_apertura || $horaEntregaStr > $tienda->hora_cierre) {
                return response()->json([
                    'message' => "Esta tienda solo acepta pedidos dentro de su horario ({$tienda->hora_apertura} a {$tienda->hora_cierre}) o desde su siguiente apertura.",
                ], 422);
            }
        }

        // 4. R12: Autoregalo prohibido (el comprador no puede ser el dueño de la tienda)
        if ($tienda->usuario_id === $comprador->id) {
            return response()->json([
                'message' => 'No puedes realizar pedidos a tu propia tienda.',
            ], 422);
        }

        // 5. R14: Calcular total en el servidor con los precios actuales en BD (incluye descuentos)
        $total = 0;
        $itemsParaGuardar = [];

        foreach ($itemsInput as $itemInput) {
            $producto = $productos->firstWhere('id', $itemInput['producto_id']);
            $cantidad = (int) $itemInput['cantidad'];
            $precio = (float) $producto->precio_efectivo;
            $subtotal = round($precio * $cantidad, 2);
            $total += $subtotal;

            $itemsParaGuardar[] = [
                'producto_id'     => $producto->id,
                'nombre_producto' => $producto->nombre,
                'precio_unitario' => $precio,
                'cantidad'        => $cantidad,
                'subtotal'        => $subtotal,
            ];
        }

        // 6. R11: Crear pedido y pedido_items en transacción garantizando unicidad de código
        $pedido = DB::transaction(function () use ($comprador, $tienda, $request, $total, $itemsParaGuardar) {
            $codigoTemp = 'PK-TMP-' . Str::random(12);

            $pedido = Pedido::create([
                'codigo'           => $codigoTemp,
                'comprador_id'     => $comprador->id,
                'tienda_id'        => $tienda->id,
                'punto_entrega_id' => $request->input('punto_entrega_id'),
                'hora_entrega'     => $request->input('hora_entrega'),
                'forma_pago'       => $request->input('forma_pago'),
                'nota'             => $request->input('nota'),
                'total'            => $total,
                'estado'           => 'pendiente',
            ]);

            // Formatear código único con padding de 6 dígitos basado en su id secuencial
            $codigoFinal = 'PK-' . str_pad((string) $pedido->id, 6, '0', STR_PAD_LEFT);
            $codigoEntrega = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $pedido->update([
                'codigo'         => $codigoFinal,
                'codigo_entrega' => Crypt::encryptString($codigoEntrega),
            ]);

            foreach ($itemsParaGuardar as $item) {
                $pedido->items()->create($item);
            }

            return $pedido;
        });

        $pedido->load(['items', 'comprador', 'tienda.usuario', 'puntoEntrega']);

        return (new PedidoResource($pedido))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * GET /api/v1/pedidos/{codigo}/qr
     * Retorna imagen del código QR de confirmación de entrega (PNG para app móvil / SVG opcional)
     */
    public function verQr(\Illuminate\Http\Request $request, string $codigo)
    {
        $pedido = Pedido::where('codigo', $codigo)->firstOrFail();
        $codigoEntrega = '0000';
        if (!empty($pedido->codigo_entrega)) {
            try {
                $codigoEntrega = Crypt::decryptString($pedido->codigo_entrega);
            } catch (\Throwable $e) {
                $codigoEntrega = '0000';
            }
        }

        // URL directa para que la cámara del celular del vendedor la reconozca al instante
        $payload = url("/app/pedidos/{$pedido->codigo}/confirmar?c={$codigoEntrega}");

        $formato = $request->query('formato', 'png');
        if ($formato === 'svg') {
            $svg = \App\Services\QrService::generarSvg($payload, 280);
            return response($svg, 200, [
                'Content-Type' => 'image/svg+xml',
                'Cache-Control' => 'no-cache, private',
            ]);
        }

        $png = \App\Services\QrService::generarPng($payload, 280);
        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'no-cache, private',
        ]);
    }
}
