<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Tienda;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ReporteTest extends TestCase
{
    use DatabaseTransactions;

    protected function crearVendedor(): array
    {
        $vendedor = Usuario::create([
            'correo'               => 'vendedor_' . uniqid() . '@utb.edu.ec',
            'nombres'              => 'Vendedor Reporte',
            'estado'               => 'activa',
            'acepto_privacidad_en' => now(),
            'whatsapp'             => '593991234567',
        ]);

        $cat = Categoria::first();
        $tienda = Tienda::create([
            'usuario_id'     => $vendedor->id,
            'nombre'         => 'Tienda Reportable ' . uniqid(),
            'slug'           => 'tienda-rep-' . uniqid(),
            'categoria_1_id' => $cat->id,
            'plan'           => 'gratis',
            'estado'         => 'activa',
        ]);

        $token = $vendedor->createToken('test')->plainTextToken;

        return [$vendedor, $tienda, $token];
    }

    protected function crearComprador(): array
    {
        $comprador = Usuario::create([
            'correo'               => 'comprador_' . uniqid() . '@utb.edu.ec',
            'nombres'              => 'Comprador Reporte',
            'estado'               => 'activa',
            'acepto_privacidad_en' => now(),
        ]);

        $token = $comprador->createToken('test')->plainTextToken;

        return [$comprador, $token];
    }

    public function test_crear_reporte_exitoso_devuelve_201_y_mensaje(): void
    {
        [$vendedor, $tienda] = $this->crearVendedor();
        [$comprador, $token] = $this->crearComprador();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/v1/tiendas/{$tienda->id}/reportes", [
                'motivo'  => 'no_entrego',
                'detalle' => 'No acudió a la entrega pactada',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Gracias. El equipo lo revisará en 24 horas',
            ]);

        $this->assertDatabaseHas('reportes', [
            'tienda_id'     => $tienda->id,
            'reportado_por' => $comprador->id,
            'motivo'        => 'no_entrego',
            'estado'        => 'nuevo',
        ]);
    }

    public function test_crear_reporte_motivo_otro_requiere_detalle_minimo_10_caracteres(): void
    {
        [$vendedor, $tienda] = $this->crearVendedor();
        [$comprador, $token] = $this->crearComprador();

        // Sin detalle -> falla validación
        $response1 = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/v1/tiendas/{$tienda->id}/reportes", [
                'motivo' => 'otro',
            ]);

        $response1->assertStatus(422)
            ->assertJsonValidationErrors(['detalle']);

        // Detalle muy corto (< 10 caracteres) -> falla
        $response2 = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/v1/tiendas/{$tienda->id}/reportes", [
                'motivo'  => 'otro',
                'detalle' => 'muy malo',
            ]);

        $response2->assertStatus(422)
            ->assertJsonValidationErrors(['detalle']);

        // Detalle adecuado (>= 10 caracteres) -> éxito 201
        $response3 = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/v1/tiendas/{$tienda->id}/reportes", [
                'motivo'  => 'otro',
                'detalle' => 'El vendedor vende productos vencidos repetidamente',
            ]);

        $response3->assertStatus(201);
    }

    public function test_r20_prohibido_autoreporte_a_tienda_propia(): void
    {
        [$vendedor, $tienda, $token] = $this->crearVendedor();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/v1/tiendas/{$tienda->id}/reportes", [
                'motivo' => 'prohibido',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'No puedes reportar tu propia tienda.',
            ]);
    }

    public function test_r20_segundo_reporte_mismo_dia_es_rechazado(): void
    {
        [$vendedor, $tienda] = $this->crearVendedor();
        [$comprador, $token] = $this->crearComprador();

        // Primer reporte -> 201
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/v1/tiendas/{$tienda->id}/reportes", [
                'motivo' => 'informacion_falsa',
            ])->assertStatus(201);

        // Segundo reporte hoy -> 422
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/v1/tiendas/{$tienda->id}/reportes", [
                'motivo' => 'informacion_falsa',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Ya reportaste esta tienda hoy.',
            ]);
    }
}
