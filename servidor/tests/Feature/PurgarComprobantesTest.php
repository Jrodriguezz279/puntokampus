<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Configuracion;
use App\Models\SolicitudPremium;
use App\Models\Tienda;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PurgarComprobantesTest extends TestCase
{
    use DatabaseTransactions;

    public function test_r25_command_purga_comprobantes_resueltos_con_mas_de_30_dias(): void
    {
        Storage::fake('local');

        $vendedor = Usuario::create([
            'correo'               => 'vendedor_' . uniqid() . '@utb.edu.ec',
            'nombres'              => 'Vendedor Purga',
            'estado'               => 'activa',
            'acepto_privacidad_en' => now(),
        ]);

        $cat = Categoria::first();
        $tienda = Tienda::create([
            'usuario_id'     => $vendedor->id,
            'nombre'         => 'Tienda Purga ' . uniqid(),
            'slug'           => 'tienda-purga-' . uniqid(),
            'categoria_1_id' => $cat->id,
            'plan'           => 'gratis',
            'estado'         => 'activa',
        ]);

        // Crear archivo simulado
        $archivoRuta = 'comprobantes/test_comprobante.jpg';
        Storage::disk('local')->put($archivoRuta, 'contenido falso');
        $this->assertTrue(Storage::disk('local')->exists($archivoRuta));

        // 1. Solicitud aprobada revisada hace 35 días
        $solicitudVieja = SolicitudPremium::create([
            'tienda_id'          => $tienda->id,
            'numero_comprobante' => 'TRF-VIEJO-' . uniqid(),
            'monto'              => 5.00,
            'foto_comprobante'   => $archivoRuta,
            'estado'             => 'aprobada',
            'revisado_en'        => now()->subDays(35),
        ]);

        $this->artisan('app:purgar-comprobantes')
            ->assertExitCode(0);

        $solicitudVieja->refresh();

        // El archivo físico debe haber sido eliminado y registrado foto_borrada_en
        $this->assertFalse(Storage::disk('local')->exists($archivoRuta));
        $this->assertNotNull($solicitudVieja->foto_borrada_en);
    }
}

