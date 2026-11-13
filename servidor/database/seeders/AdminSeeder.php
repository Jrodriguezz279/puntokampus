<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Usuario::updateOrCreate(
            ['correo' => 'admin@utb.edu.ec'],
            [
                'nombres' => 'Administrador PuntoKampus',
                'es_admin' => true,
                'password' => Hash::make('PuntoKampus2026!'),
                'estado' => 'activa',
                'acepto_privacidad_en' => now(),
            ]
        );
    }
}

