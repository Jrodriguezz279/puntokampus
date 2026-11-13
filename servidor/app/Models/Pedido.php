<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $table = 'pedidos';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'codigo',
        'comprador_id',
        'tienda_id',
        'punto_entrega_id',
        'hora_entrega',
        'forma_pago',
        'nota',
        'total',
        'estado',
        'cancelado_por',
        'estado_cambiado_en',
    ];

    protected function casts(): array
    {
        return [
            'hora_entrega' => 'datetime',
            'total' => 'decimal:2',
            'estado_cambiado_en' => 'datetime',
        ];
    }

    public function comprador()
    {
        return $this->belongsTo(Usuario::class, 'comprador_id');
    }

    public function tienda()
    {
        return $this->belongsTo(Tienda::class, 'tienda_id');
    }

    public function puntoEntrega()
    {
        return $this->belongsTo(PuntoEntrega::class, 'punto_entrega_id');
    }

    public function items()
    {
        return $this->hasMany(PedidoItem::class, 'pedido_id');
    }
}

