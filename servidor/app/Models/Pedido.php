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
        'admin_nota',
        'admin_id',
        'admin_gestionado_en',
    ];

    protected $hidden = [
        'codigo_entrega',
    ];

    protected function casts(): array
    {
        return [
            'hora_entrega'        => 'datetime',
            'total'               => 'decimal:2',
            'estado_cambiado_en'  => 'datetime',
            'codigo_bloqueado'    => 'boolean',
            'intentos_codigo'     => 'integer',
            'admin_gestionado_en' => 'datetime',
        ];
    }

    // ─── Relaciones ───────────────────────────────────────────────────────────

    public function admin()
    {
        return $this->belongsTo(Usuario::class, 'admin_id');
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

    /**
     * CAMBIO 2: Campo calculado paso_hora
     * Verdadero si el pedido sigue pendiente y la hora de entrega ya pasó.
     */
    public function getPasoHoraAttribute(): bool
    {
        return $this->estado === 'pendiente'
            && $this->hora_entrega !== null
            && now()->greaterThan($this->hora_entrega);
    }

    /**
     * CAMBIO 2: Campo calculado vence_en
     * Fecha y hora en que pasará a vencido (hora_entrega + 24 horas).
     */
    public function getVenceEnAttribute(): ?\Illuminate\Support\Carbon
    {
        if ($this->estado !== 'pendiente' || !$this->hora_entrega) {
            return null;
        }

        return $this->hora_entrega->copy()->addHours(24);
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
     * Construye la URL de WhatsApp del vendedor para este pedido con los detalles completos.
     */
    public function whatsappUrl(): string
    {
        $telefono = $this->tienda?->usuario?->whatsapp ?? '';
        $telefono = preg_replace('/[^0-9]/', '', $telefono);
        if (str_starts_with($telefono, '09') && strlen($telefono) === 10) {
            $telefono = '593' . substr($telefono, 1);
        }

        $lineas = [];
        $lineas[] = "¡Hola! Acabo de hacer el pedido *{$this->codigo}* en PuntoKampus.";
        $lineas[] = "";
        $lineas[] = "*Detalle del pedido:*";

        $this->loadMissing(['items', 'puntoEntrega', 'comprador', 'tienda.usuario']);
        if ($this->items && $this->items->isNotEmpty()) {
            foreach ($this->items as $item) {
                $subtotalFmt = number_format((float) $item->subtotal, 2);
                $lineas[] = "• {$item->cantidad}x {$item->nombre_producto} (\${$subtotalFmt})";
            }
        }

        $totalFmt = number_format((float) $this->total, 2);
        $lineas[] = "";
        $lineas[] = "*Total:* \${$totalFmt}";

        if ($this->puntoEntrega) {
            $lineas[] = "*Punto de encuentro:* {$this->puntoEntrega->nombre}";
        }

        if ($this->hora_entrega) {
            $fechaHora = $this->hora_entrega->format('d/m/Y H:i');
            $lineas[] = "*Hora de entrega:* {$fechaHora}";
        }

        if ($this->forma_pago) {
            $pagoStr = match($this->forma_pago) {
                'efectivo' => '💵 Efectivo',
                'transferencia' => '🏦 Transferencia bancaria',
                'deuna' => '📱 Deuna',
                default => ucfirst($this->forma_pago),
            };
            $lineas[] = "*Forma de pago:* {$pagoStr}";
        }

        if (!empty($this->nota)) {
            $lineas[] = "*Nota:* {$this->nota}";
        }

        if ($this->comprador?->nombre) {
            $lineas[] = "*Comprador:* {$this->comprador->nombre}";
        }

        $lineas[] = "";
        $lineas[] = "Quedo atento/a para coordinar la entrega. ¡Muchas gracias!";

        $mensaje = urlencode(implode("\n", $lineas));
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

    public function scopeConfirmadasConCodigo($query)
    {
        return $query->where('estado', 'entregado')->where('entregado_con', 'codigo');
    }

    public function scopeEntregadasAdmin($query)
    {
        return $query->where('estado', 'entregado')->where('entregado_con', 'administrador');
    }
}
