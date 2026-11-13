<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UsuarioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'correo' => $this->correo,
            'nombres' => $this->nombres,
            'codigo_estudiante' => $this->codigo_estudiante,
            'facultad' => $this->facultad,
            'carrera' => $this->carrera,
            'semestre' => $this->semestre,
            'whatsapp' => $this->whatsapp,
            'mostrar_whatsapp' => (bool) $this->mostrar_whatsapp,
            'es_admin' => (bool) $this->es_admin,
            'acepto_privacidad_en' => $this->acepto_privacidad_en?->toIso8601String(),
            'estado' => $this->estado,
            'creado_en' => $this->creado_en?->toIso8601String(),
        ];
    }
}

