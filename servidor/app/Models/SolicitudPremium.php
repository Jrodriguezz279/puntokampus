<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitudPremium extends Model
{
    protected $table = 'solicitudes_premium';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'tienda_id',
        'numero_comprobante',
        'monto',
        'foto_comprobante',
        'estado',
        'motivo_rechazo',
        'revisado_por',
        'revisado_en',
        'foto_borrada_en',
    ];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'revisado_en' => 'datetime',
            'foto_borrada_en' => 'datetime',
        ];
    }

    public function tienda()
    {
        return $this->belongsTo(Tienda::class, 'tienda_id');
    }

    public function revisadoPor()
    {
        return $this->belongsTo(Usuario::class, 'revisado_por');
    }
}
