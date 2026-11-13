<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Configuracion;
use App\Models\Tienda;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PremiumTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    protected function crearVendedor(): array
    {
        $vendedor = Usuario::create([
            'correo'               => 'vendedor_' . uniqid() . '@utb.edu.ec',
            'nombres'              => 'Vendedor Premium',
            'estado'               => 'activa',
            'acepto_privacidad_en' => now(),
            'whatsapp'             => '593991234567',
        ]);

        $cat = Categoria::first();
        $tienda = Tienda::create([
            'usuario_id'     => $vendedor->id,
            'nombre'         => 'Tienda Vendedor ' . uniqid(),
            'slug'           => 'tienda-prem-' . uniqid(),
            'categoria_1_id' => $cat->id,
            'plan'           => 'gratis',
            'estado'         => 'activa',
        ]);

        $token = $vendedor->createToken('test')->plainTextToken;

        return [$vendedor, $tienda, $token];
    }

    public function test_consultar_datos_de_pago_y_estado_premium(): void
    {
        [$vendedor, $tienda, $token] = $this->crearVendedor();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/mi-tienda/premium');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'tienda_plan',
                'datos_pago' => [
                    'precio_premium',
                    'banco',
                    'tipo_cuenta',
                    'numero_cuenta',
                    'titular_cuenta',
                ],
                'ultima_solicitud',
            ]);
    }

    public function test_solicitar_premium_exitoso(): void
    {
        [$vendedor, $tienda, $token] = $this->crearVendedor();

        $foto = UploadedFile::fake()->image('comprobante.jpg', 600, 600);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/mi-tienda/premium', [
                'numero_comprobante' => 'TRF-123456',
                'monto'              => 5.00,
                'foto_comprobante'   => $foto,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.numero_comprobante', 'TRF-123456')
            ->assertJsonPath('data.estado', 'pendiente');

        $this->assertDatabaseHas('solicitudes_premium', [
            'tienda_id'          => $tienda->id,
            'numero_comprobante' => 'TRF-123456',
            'estado'             => 'pendiente',
        ]);
    }

    public function test_r21_no_permite_otra_solicitud_mientras_haya_una_pendiente(): void
    {
        [$vendedor, $tienda, $token] = $this->crearVendedor();

        $foto1 = UploadedFile::fake()->image('comp1.jpg');
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/mi-tienda/premium', [
                'numero_comprobante' => 'TRF-0001',
                'monto'              => 5.00,
                'foto_comprobante'   => $foto1,
            ])->assertStatus(201);

        // Segunda solicitud con la primera aún pendiente -> 422
        $foto2 = UploadedFile::fake()->image('comp2.jpg');
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/mi-tienda/premium', [
                'numero_comprobante' => 'TRF-0002',
                'monto'              => 5.00,
                'foto_comprobante'   => $foto2,
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Ya tienes una solicitud en revisión.',
            ]);
    }
}

