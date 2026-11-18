<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tienda extends Model
{
    protected $table = 'tiendas';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'usuario_id',
        'nombre',
        'slug',
        'descripcion',
        'logo_url',
        'portada_url',
        'categoria_1_id',
        'categoria_2_id',
        'horario',
        'recibiendo_pedidos',
        'dias_atencion',
        'hora_apertura',
        'hora_cierre',
        'punto_entrega_id',
        'plan',
        'premium_hasta',
        'estado',
        'motivo_baja',
        'dada_de_baja_en',
    ];

    protected function casts(): array
    {
        return [
            'recibiendo_pedidos' => 'boolean',
            'premium_hasta' => 'date',
            'dada_de_baja_en' => 'datetime',
        ];
    }

    /**
     * Verifica si la tienda se encuentra abierta y recibiendo pedidos en este momento.
     */
    public function estaAbiertoAhora(): bool
    {
        if (isset($this->recibiendo_pedidos) && !$this->recibiendo_pedidos) {
            return false;
        }

        if (empty($this->hora_apertura) || empty($this->hora_cierre)) {
            return true;
        }

        $ahora = now()->timezone('America/Guayaquil');
        $horaActual = $ahora->format('H:i');

        if (!empty($this->dias_atencion)) {
            $diasConfig = strtolower($this->dias_atencion);
            $diaSemana = $ahora->dayOfWeekIso; // 1 = Lunes, 7 = Domingo

            if (str_contains($diasConfig, 'lunes a viernes') && !in_array($diaSemana, [1, 2, 3, 4, 5])) {
                return false;
            }
            if (str_contains($diasConfig, 'lunes a sabado') && !in_array($diaSemana, [1, 2, 3, 4, 5, 6])) {
                return false;
            }
        }

        return $horaActual >= $this->hora_apertura && $horaActual <= $this->hora_cierre;
    }

    /**
     * Devuelve una descripción legible de la siguiente apertura.
     */
    public function siguienteAperturaTexto(): string
    {
        if (isset($this->recibiendo_pedidos) && !$this->recibiendo_pedidos) {
            return 'Pausada (No recibe pedidos)';
        }

        if (!empty($this->hora_apertura)) {
            $dias = !empty($this->dias_atencion) ? $this->dias_atencion : 'su próximo día hábil';
            return "Abre a las {$this->hora_apertura} ({$dias})";
        }

        return 'En su próximo horario habitual';
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function categoria1()
    {
        return $this->belongsTo(Categoria::class, 'categoria_1_id');
    }

    public function categoria2()
    {
        return $this->belongsTo(Categoria::class, 'categoria_2_id');
    }

    public function puntoEntrega()
    {
        return $this->belongsTo(PuntoEntrega::class, 'punto_entrega_id');
    }

    public function productos()
    {
        return $this->hasMany(Producto::class, 'tienda_id');
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'tienda_id');
    }

    public function reportes()
    {
        return $this->hasMany(Reporte::class, 'tienda_id');
    }

    public function solicitudesPremium()
    {
        return $this->hasMany(SolicitudPremium::class, 'tienda_id');
    }

    public function getLogoUrlAttribute(?string $value): ?string
    {
        return self::formatearUrl($value);
    }

    public function getPortadaUrlAttribute(?string $value): ?string
    {
        return self::formatearUrl($value);
    }

    /**
     * Resuelve de forma robusta cualquier URL almacenada para que sea alcanzable
     * tanto en la web como en la app móvil.
     */
    public static function formatearUrl(?string $value): ?string
    {
        if (!$value) {
            return null;
        }
        $val = trim($value);
        if ($val === '') {
            return null;
        }

        // Si ya es una URL con protocolo
        if (str_starts_with($val, 'http://') || str_starts_with($val, 'https://')) {
            // Si apunta a localhost o 127.0.0.1, convertir a la URL del host actual
            if (str_contains($val, 'localhost') || str_contains($val, '127.0.0.1')) {
                $path = parse_url($val, PHP_URL_PATH);
                return asset(ltrim($path, '/'));
            }
            return $val;
        }

        // Si es una ruta relativa tipo /storage/... o storage/...
        return asset(ltrim($val, '/'));
    }
}

