<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CodigoAcceso extends Model
{
    protected $table = 'codigos_acceso';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'correo',
        'codigo_hash',
        'expira_en',
        'intentos',
        'usado_en',
    ];

    protected function casts(): array
    {
        return [
            'expira_en' => 'datetime',
            'usado_en' => 'datetime',
            'intentos' => 'integer',
        ];
    }
}

