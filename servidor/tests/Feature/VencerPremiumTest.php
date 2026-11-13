<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Tienda;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class VencerPremiumTest extends TestCase
{
    use DatabaseTransactions;

    public function test_r24_command_vence_tiendas_premium_con_fecha_expirada(): void
    {
        $vendedor = Usuario::create([
            'correo'               => 'vendedor_' . uniqid() . '@utb.edu.ec',
            'nombres'              => 'Vendedor Vencer Prem',
            'estado'               => 'activa',
            'acepto_privacidad_en' => now(),
        ]);

        $cat = Categoria::first();

        // 1. Tienda premium que ya expiró ayer -> debe pasar a gratis
        $tiendaExpirada = Tienda::create([
            'usuario_id'     => $vendedor->id,
            'nombre'         => 'Tienda Expirada ' . uniqid(),
            'slug'           => 'tienda-exp-' . uniqid(),
            'categoria_1_id' => $cat->id,
            'plan'           => 'premium',
            'premium_hasta'  => now()->subDays(1)->toDateString(),
            'estado'         => 'activa',
        ]);

        // 2. Tienda premium vigente (vence en 20 días) -> debe mantenerse premium
        $vendedor2 = Usuario::create([
            'correo'               => 'vendedor2_' . uniqid() . '@utb.edu.ec',
            'nombres'              => 'Vendedor Vigente',
            'estado'               => 'activa',
            'acepto_privacidad_en' => now(),
        ]);

        $tiendaVigente = Tienda::create([
            'usuario_id'     => $vendedor2->id,
            'nombre'         => 'Tienda Vigente ' . uniqid(),
            'slug'           => 'tienda-vig-' . uniqid(),
            'categoria_1_id' => $cat->id,
            'plan'           => 'premium',
            'premium_hasta'  => now()->addDays(20)->toDateString(),
            'estado'         => 'activa',
        ]);

        $this->artisan('app:vencer-premium')
            ->assertExitCode(0);

        $tiendaExpirada->refresh();
        $tiendaVigente->refresh();

        $this->assertEquals('gratis', $tiendaExpirada->plan);
        $this->assertEquals('premium', $tiendaVigente->plan);
    }
}

