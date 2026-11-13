<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardarTiendaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $usuario = $this->user();
        $esCreacion = $this->isMethod('POST');

        $reglas = [
            'nombre' => ['required', 'string', 'min:3', 'max:40'],
            'descripcion' => ['nullable', 'string', 'max:300'],
            'categoria_1_id' => ['required', 'exists:categorias,id'],
            'categoria_2_id' => ['nullable', 'different:categoria_1_id', 'exists:categorias,id'],
            'horario' => ['nullable', 'string', 'max:100'],
            'punto_entrega_id' => ['nullable', 'exists:puntos_entrega,id'],
        ];

        // R6: Si es creación o faltan datos de vendedor en usuario, validarlos
        if ($esCreacion && (!$usuario->whatsapp || !$usuario->codigo_estudiante)) {
            $reglas['codigo_estudiante'] = ['required', 'string', 'max:50'];
            $reglas['facultad'] = ['required', 'string', 'max:100'];
            $reglas['carrera'] = ['required', 'string', 'max:100'];
            $reglas['semestre'] = ['required', 'integer', 'between:1,10'];
            $reglas['whatsapp'] = ['required', 'string', 'regex:/^(\+?593|0)?9\d{8}$/'];
        } else {
            $reglas['codigo_estudiante'] = ['nullable', 'string', 'max:50'];
            $reglas['facultad'] = ['nullable', 'string', 'max:100'];
            $reglas['carrera'] = ['nullable', 'string', 'max:100'];
            $reglas['semestre'] = ['nullable', 'integer', 'between:1,10'];
            $reglas['whatsapp'] = ['nullable', 'string', 'regex:/^(\+?593|0)?9\d{8}$/'];
        }

        return $reglas;
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la tienda es obligatorio.',
            'nombre.min' => 'El nombre de la tienda debe tener al menos 3 caracteres.',
            'nombre.max' => 'El nombre de la tienda no puede exceder 40 caracteres.',
            'categoria_1_id.required' => 'Debes elegir una categoría principal.',
            'categoria_2_id.different' => 'La segunda categoría debe ser distinta de la primera.',
            'whatsapp.regex' => 'El número de WhatsApp no tiene un formato válido (ej. 0991234567).',
            'semestre.between' => 'El semestre debe estar entre 1 y 10.',
        ];
    }
}

