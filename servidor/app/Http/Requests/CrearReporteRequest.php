<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CrearReporteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'motivo'  => ['required', 'string', 'in:prohibido,no_entrego,informacion_falsa,trato_inadecuado,otro'],
            'detalle' => [
                'nullable',
                'string',
                'max:300',
                'required_if:motivo,otro',
                function ($attribute, $value, $fail) {
                    if ($this->input('motivo') === 'otro' && (!is_string($value) || mb_strlen(trim($value)) < 10)) {
                        $fail('Si el motivo es "otro", debes detallar la razón con al menos 10 caracteres.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'motivo.required'         => 'El motivo del reporte es obligatorio.',
            'motivo.in'               => 'El motivo seleccionado no es válido.',
            'detalle.required_if'     => 'Si el motivo es "otro", el detalle es obligatorio.',
            'detalle.max'             => 'El detalle no puede superar los 300 caracteres.',
        ];
    }
}
