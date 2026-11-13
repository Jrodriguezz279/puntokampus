<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class SolicitudPremiumResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'numero_comprobante' => $this->numero_comprobante,
            'monto'              => (float) $this->monto,
            'estado'             => $this->estado,
            'motivo_rechazo'     => $this->motivo_rechazo,
            'revisado_en'        => $this->revisado_en?->toIso8601String(),
            'foto_disponible'    => $this->foto_borrada_en === null,
            'creado_en'          => $this->creado_en?->toIso8601String(),
        ];
    }
}

