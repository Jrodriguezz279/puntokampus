<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PedidoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
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
            'cancelado_por'      => $this->cancelado_por,
            'estado_cambiado_en' => $this->estado_cambiado_en?->toIso8601String(),
            'creado_en'          => $this->creado_en?->toIso8601String(),
            'items'              => PedidoItemResource::collection($this->whenLoaded('items')),
            'whatsapp_url'       => $this->whatsappUrl(),
        ];
    }
}
