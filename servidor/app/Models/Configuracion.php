<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    protected $table = 'configuracion';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'precio_premium',
        'banco',
        'tipo_cuenta',
        'numero_cuenta',
        'titular_cuenta',
        'usuario_deuna',
        'fin_periodo_academico',
        'dias_borrado_comprobante',
        'limite_pedidos_gratis',
        'instagram_url',
        'tiktok_url',
        'facebook_url',
    ];

    protected function casts(): array
    {
        return [
            'precio_premium' => 'decimal:2',
            'fin_periodo_academico' => 'date',
            'dias_borrado_comprobante' => 'integer',
            'limite_pedidos_gratis' => 'integer',
        ];
    }
}

