<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SolicitarPremiumRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'numero_comprobante' => ['required', 'string', 'min:4', 'max:50'],
            'monto'              => ['required', 'numeric', 'min:0.01', 'max:1000'],
            'foto_comprobante'   => ['required', 'file', 'image', 'max:5120'], // Máximo 5MB
        ];
    }

    public function messages(): array
    {
        return [
            'numero_comprobante.required' => 'El número de comprobante es obligatorio.',
            'numero_comprobante.min'      => 'El número de comprobante debe tener al menos 4 caracteres.',
            'monto.required'              => 'El monto transferido es obligatorio.',
            'monto.numeric'               => 'El monto debe ser un valor numérico válido.',
            'foto_comprobante.required'   => 'Debes adjuntar la foto del comprobante de pago.',
            'foto_comprobante.image'      => 'El archivo del comprobante debe ser una imagen.',
            'foto_comprobante.max'        => 'La foto del comprobante no puede pesar más de 5MB.',
        ];
    }
}
