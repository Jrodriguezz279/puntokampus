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
        'codigo_entrega',
        'intentos_codigo',
        'codigo_bloqueado',
        'entregado_con',
    ];

    protected $hidden = [
        'codigo_entrega',
    ];

    protected function casts(): array
    {
        return [
            'hora_entrega'       => 'datetime',
            'total'              => 'decimal:2',
            'estado_cambiado_en' => 'datetime',
            'codigo_bloqueado'   => 'boolean',
            'intentos_codigo'    => 'integer',
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

    public function calificaciones()
    {
        return $this->hasMany(Calificacion::class, 'pedido_id');
    }

    public function calificacionATienda()
    {
        return $this->hasOne(Calificacion::class, 'pedido_id')->where('direccion', 'a_tienda');
    }

    public function calificacionAComprador()
    {
        return $this->hasOne(Calificacion::class, 'pedido_id')->where('direccion', 'a_comprador');
    }

    public function fechaLimiteCalificacion(): ?\Illuminate\Support\Carbon
    {
        $fecha = $this->estado_cambiado_en ?? $this->actualizado_en ?? $this->creado_en;
        return $fecha ? $fecha->copy()->addDays(3) : null;
    }

    public function enPlazoCalificacion(): bool
    {
        if ($this->estado !== 'entregado') {
            return false;
        }
        $limite = $this->fechaLimiteCalificacion();
        return $limite ? now()->lte($limite) : false;
    }

    public function calificacionesSonVisibles(): bool
    {
        if ($this->estado !== 'entregado') {
            return false;
        }

        $limite = $this->fechaLimiteCalificacion();
        if ($limite && now()->gte($limite)) {
            return true;
        }

        $tieneATienda = $this->calificaciones()->where('direccion', 'a_tienda')->exists();
        $tieneAComprador = $this->calificaciones()->where('direccion', 'a_comprador')->exists();

        return $tieneATienda && $tieneAComprador;
    }

    public function puedeCalificarComprador(): bool
    {
        return $this->enPlazoCalificacion() && !$this->calificaciones()->where('direccion', 'a_tienda')->exists();
    }

    public function puedeCalificarVendedor(): bool
    {
        return $this->enPlazoCalificacion() && !$this->calificaciones()->where('direccion', 'a_comprador')->exists();
    }

    public function items()
    {
        return $this->hasMany(PedidoItem::class, 'pedido_id');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

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

    /**
     * Retorna el código de entrega descifrado o null.
     */
    public function getCodigoEntregaDescifradoAttribute(): ?string
    {
        if (empty($this->codigo_entrega)) {
            return null;
        }

        try {
            return \Illuminate\Support\Facades\Crypt::decryptString($this->codigo_entrega);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Payload URL para el código QR de confirmación (directamente escaneable por cualquier cámara).
     */
    public function getQrPayloadAttribute(): ?string
    {
        $pin = $this->codigo_entrega_descifrado;
        if (!$pin) {
            return null;
        }
        return url("/app/pedidos/{$this->codigo}/confirmar?c={$pin}");
    }

    /**
     * Verifica el código de entrega ingresado por el vendedor.
     * Incrementa intentos y bloquea tras 5 fallidos.
     */
    public function verificarCodigoEntrega(string $intento): bool
    {
        if ($this->codigo_bloqueado || !$this->codigo_entrega) {
            return false;
        }

        try {
            $codigoReal = \Illuminate\Support\Facades\Crypt::decryptString($this->codigo_entrega);
        } catch (\Exception $e) {
            return false;
        }

        if (trim($codigoReal) !== trim($intento)) {
            $this->intentos_codigo += 1;
            if ($this->intentos_codigo >= 5) {
                $this->codigo_bloqueado = true;
            }
            $this->save();
            return false;
        }

        return true;
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }
}
