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
            'hora_entrega'       => 'datetime',
            'total'              => 'decimal:2',
            'estado_cambiado_en' => 'datetime',
        ];
    }

    // ─── Relaciones ───────────────────────────────────────────────────────────

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

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Genera el siguiente código PK-XXXXXX usando el último número secuencial.
     */
    public static function generarCodigo(): string
    {
        $ultimoId = static::max('id') ?? 0;
        return 'PK-' . str_pad($ultimoId + 1, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Construye la URL de WhatsApp del vendedor para este pedido.
     */
    public function whatsappUrl(): string
    {
        $telefono = $this->tienda?->usuario?->whatsapp ?? '';
        $mensaje  = urlencode("Hola, hice el pedido {$this->codigo} en PuntoKampus");
        return "https://wa.me/{$telefono}?text={$mensaje}";
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }
}
