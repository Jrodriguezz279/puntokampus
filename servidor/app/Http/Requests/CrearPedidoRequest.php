<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CrearPedidoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items'                   => ['required', 'array', 'min:1'],
            'items.*.producto_id'     => ['required', 'integer', 'exists:productos,id'],
            'items.*.cantidad'        => ['required', 'integer', 'min:1', 'max:20'],
            'hora_entrega'            => ['required', 'date', 'after:now'],
            'punto_entrega_id'        => ['required', 'integer', 'exists:puntos_entrega,id'],
            'forma_pago'              => ['required', 'string', 'in:efectivo,transferencia,deuna'],
            'nota'                    => ['nullable', 'string', 'max:200'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required'              => 'Debe incluir al menos un producto en el pedido.',
            'items.min'                   => 'Debe incluir al menos un producto en el pedido.',
            'items.*.producto_id.exists'  => 'Uno de los productos seleccionados no existe.',
            'items.*.cantidad.min'        => 'La cantidad mínima por producto es 1.',
            'items.*.cantidad.max'        => 'La cantidad máxima por producto es 20.',
            'hora_entrega.required'       => 'La hora de entrega es requerida.',
            'hora_entrega.after'          => 'La hora de entrega debe ser una fecha y hora futura.',
            'punto_entrega_id.required'   => 'El punto de entrega es requerido.',
            'punto_entrega_id.exists'     => 'El punto de entrega seleccionado no existe.',
            'forma_pago.required'         => 'La forma de pago es requerida.',
            'forma_pago.in'               => 'La forma de pago debe ser efectivo, transferencia o deuna.',
            'nota.max'                    => 'La nota no puede superar los 200 caracteres.',
        ];
    }
}

