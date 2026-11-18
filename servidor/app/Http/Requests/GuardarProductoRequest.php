<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardarProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'min:3', 'max:60'],
            'descripcion' => ['nullable', 'string', 'max:300'],
            'precio' => ['required', 'numeric', 'min:0.01', 'max:500'],
            'precio_descuento' => ['nullable', 'numeric', 'min:0.01', 'max:500'],
            'descuento_activo' => ['nullable', 'boolean'],
            'disponible' => ['nullable', 'boolean'],
            'foto' => ['nullable', 'image', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del producto es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 3 caracteres.',
            'nombre.max' => 'El nombre no puede tener más de 60 caracteres.',
            'precio.required' => 'El precio es obligatorio.',
            'precio.min' => 'El precio debe ser mayor a 0.',
            'precio.max' => 'El precio no puede exceder $500.',
        ];
    }
}

