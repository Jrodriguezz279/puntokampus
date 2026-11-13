<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PuntoEntregaSeeder extends Seeder
{
    public function run(): void
    {
        $puntos = [
            ['nombre' => 'Biblioteca Central', 'descripcion' => 'Entrada principal de la biblioteca', 'orden' => 1],
            ['nombre' => 'Entrada FAFI', 'descripcion' => 'Facultad de Ciencias Agropecuarias', 'orden' => 2],
            ['nombre' => 'Entrada FCS', 'descripcion' => 'Facultad de Ciencias de la Salud', 'orden' => 3],
            ['nombre' => 'Entrada FCJSE', 'descripcion' => 'Facultad de Ciencias Jurídicas, Sociales y de la Educación', 'orden' => 4],
            ['nombre' => 'Entrada FCE', 'descripcion' => 'Facultad de Ciencias de la Educación', 'orden' => 5],
        ];

        $now = now();
        foreach ($puntos as $pto) {
            DB::table('puntos_entrega')->updateOrInsert(
                ['nombre' => $pto['nombre']],
                [
                    'descripcion' => $pto['descripcion'],
                    'activa' => true,
                    'orden' => $pto['orden'],
                    'creado_en' => $now,
                    'actualizado_en' => $now,
                ]
            );
        }
    }
}

