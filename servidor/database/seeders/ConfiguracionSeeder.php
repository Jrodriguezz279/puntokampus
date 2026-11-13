<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConfiguracionSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        DB::table('configuracion')->updateOrInsert(
            ['id' => 1],
            [
                'precio_premium' => 5.00,
                'banco' => 'Banco Pichincha',
                'tipo_cuenta' => 'Ahorros',
                'numero_cuenta' => '2200000000',
                'titular_cuenta' => 'Equipo PuntoKampus',
                'usuario_deuna' => 'puntokampus',
                'fin_periodo_academico' => '2027-03-31',
                'dias_borrado_comprobante' => 30,
                'limite_pedidos_gratis' => null, // Vacío en tramo 1
                'creado_en' => $now,
                'actualizado_en' => $now,
            ]
        );
    }
}
