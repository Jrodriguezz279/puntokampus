<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $seeders = [
            CategoriaSeeder::class,
            PuntoEntregaSeeder::class,
            ConfiguracionSeeder::class,
            AdminSeeder::class,
        ];

        // Seeders de prueba solo en entorno local de desarrollo
        if (app()->environment('local')) {
            $seeders[] = DemoSeeder::class;
        }

        $this->call($seeders);
    }
}
