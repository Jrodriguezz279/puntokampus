<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PuntoEntrega extends Model
{
    protected $table = 'puntos_entrega';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'nombre',
        'descripcion',
        'activa',
        'orden',
    ];

    protected function casts(): array
    {
        return [
            'activa' => 'boolean',
            'orden' => 'integer',
        ];
    }

    public function tiendas()
    {
        return $this->hasMany(Tienda::class, 'punto_entrega_id');
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'punto_entrega_id');
    }
}
