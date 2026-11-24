<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Crypt;

class PedidoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $codigoEntrega = null;
        $usuario = $request->user();
        if ($usuario && (int) $usuario->id === (int) $this->comprador_id) {
            if ($this->estado === 'pendiente' && !$this->codigo_bloqueado && !empty($this->codigo_entrega)) {
                try {
                    $codigoEntrega = Crypt::decryptString($this->codigo_entrega);
                } catch (\Throwable $e) {
                    $codigoEntrega = null;
                }
            }
        }

        $data = [
            'id'                 => $this->id,
            'codigo'             => $this->codigo,
            'comprador'          => new UsuarioResource($this->whenLoaded('comprador')),
            'tienda'             => new TiendaResource($this->whenLoaded('tienda')),
            'punto_entrega'      => new PuntoEntregaResource($this->whenLoaded('puntoEntrega')),
            'hora_entrega'       => $this->hora_entrega?->toIso8601String(),
            'forma_pago'         => $this->forma_pago,
            'nota'               => $this->nota,
            'total'              => (float) $this->total,
            'estado'             => $this->estado,
            'codigo_bloqueado'   => (bool) $this->codigo_bloqueado,
            'cancelado_por'      => $this->cancelado_por,
            'estado_cambiado_en' => $this->estado_cambiado_en?->toIso8601String(),
            'creado_en'          => $this->creado_en?->toIso8601String(),
            'items'              => PedidoItemResource::collection($this->whenLoaded('items')),
            'whatsapp_url'       => $this->whatsappUrl(),
        ];

        if ($codigoEntrega !== null) {
            $data['codigo_entrega'] = $codigoEntrega;
        }

        // Calificaciones bidireccionales (Fase 3: Estrellas)
        $esComprador = $usuario && (int) $usuario->id === (int) $this->comprador_id;
        $esVendedor  = $usuario && $this->tienda && (int) $usuario->id === (int) $this->tienda->usuario_id;
        $visibles    = $this->calificacionesSonVisibles();

        if ($esComprador) {
            $data['puede_calificar']       = $this->puedeCalificarComprador();
            $data['calificacion_recibida'] = $visibles ? $this->calificacionAComprador?->estrellas : null;
            $data['calificacion_dada']     = $this->calificacionATienda?->estrellas;

            // Fase 4: QR de Deuna solo para el comprador con pago deuna
            if ($this->forma_pago === 'deuna' && $this->tienda && !empty($this->tienda->deuna_qr_url)) {
                $data['deuna_qr_url'] = $this->tienda->deuna_qr_url;
            }
        }

        if ($esVendedor) {
            $data['comprador_estrellas']   = $this->comprador?->estrellasPromedioComprador();
            $data['puede_calificar']       = $this->puedeCalificarVendedor();
            $data['calificacion_recibida'] = $visibles ? $this->calificacionATienda?->estrellas : null;
            $data['calificacion_dada']     = $this->calificacionAComprador?->estrellas;
        }

        return $data;
    }
}

