<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            ['nombre' => 'Comida', 'orden' => 1],
            ['nombre' => 'Postres y dulces', 'orden' => 2],
            ['nombre' => 'Bebidas', 'orden' => 3],
            ['nombre' => 'Ropa y accesorios', 'orden' => 4],
            ['nombre' => 'Tecnología', 'orden' => 5],
            ['nombre' => 'Útiles y papelería', 'orden' => 6],
            ['nombre' => 'Servicios', 'orden' => 7],
            ['nombre' => 'Otros', 'orden' => 8],
        ];

        $now = now();
        foreach ($categorias as $cat) {
            DB::table('categorias')->updateOrInsert(
                ['nombre' => $cat['nombre']],
                [
                    'activa' => true,
                    'orden' => $cat['orden'],
                    'creado_en' => $now,
                    'actualizado_en' => $now,
                ]
            );
        }
    }
}
