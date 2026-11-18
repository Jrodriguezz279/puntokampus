<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'productos';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'tienda_id',
        'nombre',
        'descripcion',
        'precio',
        'precio_descuento',
        'descuento_activo',
        'disponible',
        'eliminado_en',
    ];

    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'precio_descuento' => 'decimal:2',
            'descuento_activo' => 'boolean',
            'disponible' => 'boolean',
            'eliminado_en' => 'datetime',
        ];
    }

    /**
     * Determina si el producto tiene un descuento válido activo.
     */
    public function getTieneDescuentoAttribute(): bool
    {
        return $this->descuento_activo
            && $this->precio_descuento !== null
            && $this->precio_descuento > 0
            && $this->precio_descuento < $this->precio;
    }

    /**
     * Retorna el precio real a cobrar (con o sin descuento).
     */
    public function getPrecioEfectivoAttribute(): float
    {
        return (float) ($this->tiene_descuento ? $this->precio_descuento : $this->precio);
    }

    /**
     * Porcentaje de descuento.
     */
    public function getPorcentajeDescuentoAttribute(): int
    {
        if (!$this->tiene_descuento || $this->precio <= 0) {
            return 0;
        }

        return (int) round((1 - ((float) $this->precio_descuento / (float) $this->precio)) * 100);
    }

    public function tienda()
    {
        return $this->belongsTo(Tienda::class, 'tienda_id');
    }

    public function fotos()
    {
        return $this->hasMany(ProductoFoto::class, 'producto_id')->orderBy('orden');
    }
}

