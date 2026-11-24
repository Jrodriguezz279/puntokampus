<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Calificacion extends Model
{
    protected $table = 'calificaciones';

    public $timestamps = false;

    protected $fillable = [
        'pedido_id',
        'direccion',
        'autor_id',
        'estrellas',
        'creada_en',
    ];

    protected function casts(): array
    {
        return [
            'estrellas' => 'integer',
            'creada_en' => 'datetime',
        ];
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function autor()
    {
        return $this->belongsTo(Usuario::class, 'autor_id');
    }

    /**
     * Determina si esta calificación es visible públicamente / para la otra parte.
     * Es visible si:
     * 1. La otra parte ya calificó también (ambas direcciones existen), o
     * 2. Pasaron 3 días desde la entrega del pedido.
     */
    public function esVisible(): bool
    {
        $pedido = $this->pedido;
        if (!$pedido) {
            return false;
        }

        return $pedido->calificacionesSonVisibles();
    }
}
