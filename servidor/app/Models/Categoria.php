<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $table = 'categorias';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'nombre',
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
        return $this->hasMany(Tienda::class, 'categoria_1_id')
            ->orWhere('categoria_2_id', $this->id);
    }
}
