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

        return $data;
    }
}

