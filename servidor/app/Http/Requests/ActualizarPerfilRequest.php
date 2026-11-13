<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ActualizarPerfilRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $usuario = $this->user();
        $reglas = [
            'nombres' => ['sometimes', 'required', 'string', 'min:3', 'max:100'],
            'codigo_estudiante' => ['nullable', 'string', 'max:50'],
            'facultad' => ['nullable', 'string', 'max:100'],
            'carrera' => ['nullable', 'string', 'max:100'],
            'semestre' => ['nullable', 'integer', 'between:1,10'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'mostrar_whatsapp' => ['nullable', 'boolean'],
        ];

        // R3: Si nunca ha aceptado la política de privacidad, es obligatoria
        if (!$usuario || !$usuario->acepto_privacidad_en) {
            $reglas['acepto_privacidad'] = ['required', 'accepted'];
        }

        return $reglas;
    }

    public function messages(): array
    {
        return [
            'nombres.required' => 'Tus nombres son obligatorios.',
            'acepto_privacidad.required' => 'Debes aceptar la política de privacidad para continuar.',
            'acepto_privacidad.accepted' => 'Debes aceptar la política de privacidad para continuar.',
            'semestre.between' => 'El semestre debe estar entre 1 y 10.',
        ];
    }
}

