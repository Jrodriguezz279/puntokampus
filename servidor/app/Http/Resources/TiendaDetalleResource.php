<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TiendaDetalleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'slug' => $this->slug,
            'descripcion' => $this->descripcion,
            'logo_url' => $this->logo_url,
            'portada_url' => $this->plan === 'premium' ? $this->portada_url : null,
            'plan' => $this->plan,
            'insignia' => $this->plan === 'premium' ? 'Sacó 10' : 'Matriculado',
            'estado' => $this->estado,
            'horario' => $this->horario,
            'recibiendo_pedidos' => (bool) ($this->recibiendo_pedidos ?? true),
            'dias_atencion' => $this->dias_atencion,
            'hora_apertura' => $this->hora_apertura,
            'hora_cierre' => $this->hora_cierre,
            'esta_abierto' => $this->estaAbiertoAhora(),
            'siguiente_apertura' => $this->siguienteAperturaTexto(),
            'categoria_1' => new CategoriaResource($this->whenLoaded('categoria1')),
            'categoria_2' => new CategoriaResource($this->whenLoaded('categoria2')),
            'punto_entrega' => new PuntoEntregaResource($this->whenLoaded('puntoEntrega')),
            'whatsapp' => ($this->usuario && $this->usuario->mostrar_whatsapp) ? $this->usuario->whatsapp : null,
            'estrellas_promedio' => $this->estrellasPromedio(),
            'estrellas_total'    => $this->estrellasTotal(),
            'productos' => ProductoResource::collection(
                $this->productos->whereNull('eliminado_en')->load('fotos')
            ),
        ];
    }
}

