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
        'disponible',
        'eliminado_en',
    ];

    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'disponible' => 'boolean',
            'eliminado_en' => 'datetime',
        ];
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
