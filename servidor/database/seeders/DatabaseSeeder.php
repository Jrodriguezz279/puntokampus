<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CategoriaSeeder::class,
            PuntoEntregaSeeder::class,
            ConfiguracionSeeder::class,
            AdminSeeder::class,
        ]);
    }
}
