<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $adminPassword = env('ADMIN_DEFAULT_PASSWORD', 'AdminUtb2026!#Secure');

        Usuario::updateOrCreate(
            ['correo' => 'admin@utb.edu.ec'],
            [
                'nombres' => 'Administrador PuntoKampus',
                'es_admin' => true,
                'password' => Hash::make($adminPassword),
                'estado' => 'activa',
                'acepto_privacidad_en' => now(),
            ]
        );
    }
}

