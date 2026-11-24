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
        'deuna_qr_url',
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
            $diasConfig = strtolower(trim($this->dias_atencion));
            $diaSemana = $ahora->dayOfWeekIso; // 1 = Lunes, 7 = Domingo

            if (str_starts_with($diasConfig, '[')) {
                $diasArray = json_decode($diasConfig, true);
                if (is_array($diasArray) && !empty($diasArray)) {
                    if (!in_array($diaSemana, array_map('intval', $diasArray))) {
                        return false;
                    }
                }
            } else {
                if (str_contains($diasConfig, 'lunes a viernes') && !in_array($diaSemana, [1, 2, 3, 4, 5])) {
                    return false;
                }
                if (str_contains($diasConfig, 'lunes a sabado') && !in_array($diaSemana, [1, 2, 3, 4, 5, 6])) {
                    return false;
                }
            }
        }

        $horaApertura = preg_match('/^(\d{1,2}):(\d{2})/', $this->hora_apertura, $mA) ? sprintf('%02d:%02d', (int)$mA[1], (int)$mA[2]) : $this->hora_apertura;
        $horaCierre = preg_match('/^(\d{1,2}):(\d{2})/', $this->hora_cierre, $mC) ? sprintf('%02d:%02d', (int)$mC[1], (int)$mC[2]) : $this->hora_cierre;
        return $horaActual >= $horaApertura && $horaActual <= $horaCierre;
    }

    /**
     * Devuelve una descripción legible de los días de atención.
     */
    public function diasAtencionLegible(): string
    {
        if (empty($this->dias_atencion)) {
            return 'días hábiles';
        }

        $val = trim($this->dias_atencion);
        if (str_starts_with($val, '[')) {
            $diasArray = json_decode($val, true);
            if (is_array($diasArray) && !empty($diasArray)) {
                $mapa = [1 => 'Lun', 2 => 'Mar', 3 => 'Mié', 4 => 'Jue', 5 => 'Vie', 6 => 'Sáb', 7 => 'Dom'];
                if ($diasArray === [1, 2, 3, 4, 5]) {
                    return 'Lun a Vie';
                }
                if ($diasArray === [1, 2, 3, 4, 5, 6]) {
                    return 'Lun a Sáb';
                }
                $nombres = array_map(fn($d) => $mapa[(int) $d] ?? (string) $d, $diasArray);
                return implode(', ', $nombres);
            }
        }

        return $this->dias_atencion;
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
            $dias = $this->diasAtencionLegible();
            return "Abre a las {$this->hora_apertura} ({$dias})";
        }

        return 'En su próximo horario habitual';
    }

    public function calificacionesVisiblesQuery()
    {
        return Calificacion::where('direccion', 'a_tienda')
            ->whereHas('pedido', function ($q) {
                $q->where('tienda_id', $this->id)
                  ->where('estado', 'entregado')
                  ->where(function ($sub) {
                      $sub->where('estado_cambiado_en', '<=', now()->subDays(3))
                          ->orWhereHas('calificaciones', function ($c) {
                              $c->where('direccion', 'a_comprador');
                          });
                  });
            });
    }

    public function estrellasTotal(): ?int
    {
        $total = $this->calificacionesVisiblesQuery()->count();
        return $total >= 3 ? $total : null;
    }

    public function estrellasPromedio(): ?float
    {
        $total = $this->calificacionesVisiblesQuery()->count();
        if ($total < 3) {
            return null;
        }
        $avg = $this->calificacionesVisiblesQuery()->avg('estrellas');
        return $avg !== null ? round((float) $avg, 1) : null;
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

