<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TiendaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = [
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
            'estrellas_promedio' => $this->estrellasPromedio(),
            'estrellas_total'    => $this->estrellasTotal(),
            'categoria_1' => $this->categoria1 ? new CategoriaResource($this->categoria1) : null,
            'categoria_2' => $this->categoria2 ? new CategoriaResource($this->categoria2) : null,
            'punto_entrega' => $this->puntoEntrega ? new PuntoEntregaResource($this->puntoEntrega) : null,
            'whatsapp' => ($this->usuario && $this->usuario->mostrar_whatsapp) ? $this->usuario->whatsapp : null,
            'productos' => ProductoResource::collection($this->whenLoaded('productos')),
        ];

        // Solo el dueño de la tienda puede ver su propio QR de Deuna configurado
        if ($request->user() && (int) $request->user()->id === (int) $this->usuario_id) {
            $data['deuna_qr_url'] = $this->deuna_qr_url;
        }

        return $data;
    }
}

