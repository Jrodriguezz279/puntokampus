<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerificarCodigoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'correo' => ['required', 'string', 'email'],
            'codigo' => ['required', 'string', 'size:6'],
            'dispositivo' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'correo.required' => 'El correo es obligatorio.',
            'codigo.required' => 'El código es obligatorio.',
            'codigo.size' => 'El código debe tener 6 dígitos.',
        ];
    }
}

