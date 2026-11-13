<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmarBorradoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'codigo' => ['required', 'string', 'size:6', 'regex:/^\d{6}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'codigo.required' => 'El código de confirmación es obligatorio.',
            'codigo.size'     => 'El código debe tener exactamente 6 dígitos.',
            'codigo.regex'    => 'El código solo puede contener dígitos numéricos.',
        ];
    }
}

