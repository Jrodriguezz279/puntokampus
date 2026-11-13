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
            'premium_hasta' => 'date',
            'dada_de_baja_en' => 'datetime',
        ];
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
}

