<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable implements FilamentUser
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
}
