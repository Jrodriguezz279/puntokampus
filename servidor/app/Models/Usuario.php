<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable implements FilamentUser, HasName
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'usuarios';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'correo',
        'nombres',
        'codigo_estudiante',
        'facultad',
        'carrera',
        'semestre',
        'whatsapp',
        'mostrar_whatsapp',
        'es_admin',
        'password',
        'acepto_privacidad_en',
        'estado',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'mostrar_whatsapp' => 'boolean',
            'es_admin' => 'boolean',
            'acepto_privacidad_en' => 'datetime',
            'semestre' => 'integer',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return (bool) $this->es_admin && $this->estado === 'activa';
    }

    public function getFilamentName(): string
    {
        return $this->nombres ?: $this->correo;
    }

    public function tienda()
    {
        return $this->hasOne(Tienda::class, 'usuario_id');
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'comprador_id');
    }

    public function reportes()
    {
        return $this->hasMany(Reporte::class, 'reportado_por');
    }

    public function calificacionesVisiblesCompradorQuery()
    {
        return Calificacion::where('direccion', 'a_comprador')
            ->whereHas('pedido', function ($q) {
                $q->where('comprador_id', $this->id)
                  ->where('estado', 'entregado')
                  ->where(function ($sub) {
                      $sub->where('estado_cambiado_en', '<=', now()->subDays(3))
                          ->orWhereHas('calificaciones', function ($c) {
                              $c->where('direccion', 'a_tienda');
                          });
                  });
            });
    }

    public function estrellasTotalComprador(): ?int
    {
        $total = $this->calificacionesVisiblesCompradorQuery()->count();
        return $total > 0 ? $total : null;
    }

    public function estrellasPromedioComprador(): ?float
    {
        $total = $this->calificacionesVisiblesCompradorQuery()->count();
        if ($total < 1) {
            return null;
        }
        $avg = $this->calificacionesVisiblesCompradorQuery()->avg('estrellas');
        return $avg !== null ? round((float) $avg, 1) : null;
    }
}

