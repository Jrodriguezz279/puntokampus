<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reporte extends Model
{
    protected $table = 'reportes';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'tienda_id',
        'reportado_por',
        'motivo',
        'detalle',
        'estado',
        'revisado_por',
        'revisado_en',
        'nota_admin',
    ];

    protected function casts(): array
    {
        return [
            'revisado_en' => 'datetime',
        ];
    }

    public function tienda()
    {
        return $this->belongsTo(Tienda::class, 'tienda_id');
    }

    public function reportadoPor()
    {
        return $this->belongsTo(Usuario::class, 'reportado_por');
    }

    public function revisadoPor()
    {
        return $this->belongsTo(Usuario::class, 'revisado_por');
    }
}

