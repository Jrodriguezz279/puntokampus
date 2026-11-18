<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tienda_id' => $this->tienda_id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'precio' => (float) $this->precio,
            'precio_descuento' => $this->precio_descuento !== null ? (float) $this->precio_descuento : null,
            'tiene_descuento' => $this->tiene_descuento,
            'descuento_porcentaje' => $this->porcentaje_descuento,
            'precio_efectivo' => $this->precio_efectivo,
            'disponible' => (bool) $this->disponible,
            'fotos' => ProductoFotoResource::collection($this->whenLoaded('fotos')),
            'creado_en' => $this->creado_en?->toIso8601String(),
        ];
    }
}

