<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubirFotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'foto' => ['required', 'image', 'max:10240'], // hasta 10MB, se procesará y reducirá
        ];
    }

    public function messages(): array
    {
        return [
            'foto.required' => 'Debes seleccionar una imagen.',
            'foto.image' => 'El archivo debe ser una imagen válida (jpg, png, webp).',
            'foto.max' => 'La imagen no puede pesar más de 10 MB.',
        ];
    }
}

