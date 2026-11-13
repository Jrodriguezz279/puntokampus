<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\PuntoEntrega;
use App\Models\Tienda;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PedidoCrearTest extends TestCase
{
    use DatabaseTransactions;

    protected function crearVendedor(string $nombreTienda = 'Tienda Test'): array
    {
        $vendedor = Usuario::create([
            'correo'               => 'vendedor_' . uniqid() . '@utb.edu.ec',
            'nombres'              => 'Vendedor Test',
            'estado'               => 'activa',
            'acepto_privacidad_en' => now(),
            'whatsapp'             => '593991234567',
        ]);

        $cat = Categoria::first();
        $tienda = Tienda::create([
            'usuario_id'     => $vendedor->id,
            'nombre'         => $nombreTienda . ' ' . uniqid(),
            'slug'           => 'tienda-' . uniqid(),
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
            'nombres'              => 'Comprador Test',
            'estado'               => 'activa',
            'acepto_privacidad_en' => now(),
            'whatsapp'             => '593998765432',
        ]);

        $token = $comprador->createToken('test')->plainTextToken;

        return [$comprador, $token];
    }

    public function test_crear_pedido_exitoso_genera_codigo_y_whatsapp_url(): void
    {
        [$vendedor, $tienda] = $this->crearVendedor();
        [$comprador, $token] = $this->crearComprador();
        $punto = PuntoEntrega::first();

        $producto = Producto::create([
            'tienda_id'   => $tienda->id,
            'nombre'      => 'Empanada de Verde',
            'precio'      => 1.50,
            'disponible'  => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/pedidos', [
                'items' => [
                    ['producto_id' => $producto->id, 'cantidad' => 2],
                ],
                'hora_entrega'     => now()->addHours(2)->toIso8601String(),
                'punto_entrega_id' => $punto->id,
                'forma_pago'       => 'efectivo',
                'nota'             => 'Con bastante ají por favor',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'codigo',
                    'total',
                    'estado',
                    'forma_pago',
                    'whatsapp_url',
                    'items',
                ],
            ]);

        $data = $response->json('data');
        $this->assertMatchesRegularExpression('/^PK-\d{6}$/', $data['codigo']);
        $this->assertEquals(3.00, $data['total']);
        $this->assertEquals('pendiente', $data['estado']);
        $this->assertStringContainsString('wa.me/593991234567', $data['whatsapp_url']);
        $this->assertStringContainsString($data['codigo'], $data['whatsapp_url']);
        $this->assertCount(1, $data['items']);
        $this->assertEquals(2, $data['items'][0]['cantidad']);
        $this->assertEquals(3.00, $data['items'][0]['subtotal']);
    }

    public function test_r12_autoregalo_prohibido_rechaza_pedido(): void
    {
        [$vendedor, $tienda, $tokenVendedor] = $this->crearVendedor();
        $punto = PuntoEntrega::first();

        $producto = Producto::create([
            'tienda_id'   => $tienda->id,
            'nombre'      => 'Sánduche de Pollo',
            'precio'      => 2.50,
            'disponible'  => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $tokenVendedor)
            ->postJson('/api/v1/pedidos', [
                'items' => [
                    ['producto_id' => $producto->id, 'cantidad' => 1],
                ],
                'hora_entrega'     => now()->addHours(2)->toIso8601String(),
                'punto_entrega_id' => $punto->id,
                'forma_pago'       => 'deuna',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'No puedes realizar pedidos a tu propia tienda.',
            ]);
    }

    public function test_r11_productos_de_multiples_tiendas_rechazados(): void
    {
        [$vendedor1, $tienda1] = $this->crearVendedor('Tienda 1');
        [$vendedor2, $tienda2] = $this->crearVendedor('Tienda 2');
        [$comprador, $token]   = $this->crearComprador();
        $punto = PuntoEntrega::first();

        $prod1 = Producto::create([
            'tienda_id'  => $tienda1->id,
            'nombre'     => 'Producto T1',
            'precio'     => 1.00,
            'disponible' => true,
        ]);

        $prod2 = Producto::create([
            'tienda_id'  => $tienda2->id,
            'nombre'     => 'Producto T2',
            'precio'     => 2.00,
            'disponible' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/pedidos', [
                'items' => [
                    ['producto_id' => $prod1->id, 'cantidad' => 1],
                    ['producto_id' => $prod2->id, 'cantidad' => 1],
                ],
                'hora_entrega'     => now()->addHours(2)->toIso8601String(),
                'punto_entrega_id' => $punto->id,
                'forma_pago'       => 'efectivo',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Todos los productos del pedido deben pertenecer a la misma tienda.',
            ]);
    }

    public function test_r14_precio_enviado_por_cliente_ignorado_total_calculado_en_servidor(): void
    {
        [$vendedor, $tienda] = $this->crearVendedor();
        [$comprador, $token] = $this->crearComprador();
        $punto = PuntoEntrega::first();

        $producto = Producto::create([
            'tienda_id'  => $tienda->id,
            'nombre'     => 'Almuerzo Estudiantil',
            'precio'     => 3.50,
            'disponible' => true,
        ]);

        // El cliente intenta enviar precio alterado de $0.05 y subtotal fraudulento
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/pedidos', [
                'items' => [
                    [
                        'producto_id' => $producto->id,
                        'cantidad'    => 2,
                        'precio'      => 0.05,
                        'subtotal'    => 0.10,
                    ],
                ],
                'hora_entrega'     => now()->addHours(3)->toIso8601String(),
                'punto_entrega_id' => $punto->id,
                'forma_pago'       => 'transferencia',
                'total'            => 0.10,
            ]);

        $response->assertStatus(201);
        $this->assertEquals(7.00, $response->json('data.total'));
    }

    public function test_producto_no_disponible_o_eliminado_falla(): void
    {
        [$vendedor, $tienda] = $this->crearVendedor();
        [$comprador, $token] = $this->crearComprador();
        $punto = PuntoEntrega::first();

        $prodAgotado = Producto::create([
            'tienda_id'  => $tienda->id,
            'nombre'     => 'Hamburguesa Agotada',
            'precio'     => 4.00,
            'disponible' => false,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/pedidos', [
                'items' => [
                    ['producto_id' => $prodAgotado->id, 'cantidad' => 1],
                ],
                'hora_entrega'     => now()->addHours(2)->toIso8601String(),
                'punto_entrega_id' => $punto->id,
                'forma_pago'       => 'efectivo',
            ]);

        $response->assertStatus(422);

        $prodEliminado = Producto::create([
            'tienda_id'    => $tienda->id,
            'nombre'       => 'Postre Eliminado',
            'precio'       => 2.00,
            'disponible'   => true,
            'eliminado_en' => now(),
        ]);

        $response2 = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/pedidos', [
                'items' => [
                    ['producto_id' => $prodEliminado->id, 'cantidad' => 1],
                ],
                'hora_entrega'     => now()->addHours(2)->toIso8601String(),
                'punto_entrega_id' => $punto->id,
                'forma_pago'       => 'efectivo',
            ]);

        $response2->assertStatus(422);
    }

    public function test_a10_hora_entrega_pasada_falla_validacion(): void
    {
        [$vendedor, $tienda] = $this->crearVendedor();
        [$comprador, $token] = $this->crearComprador();
        $punto = PuntoEntrega::first();

        $producto = Producto::create([
            'tienda_id'  => $tienda->id,
            'nombre'     => 'Café Pasado',
            'precio'     => 1.00,
            'disponible' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/pedidos', [
                'items' => [
                    ['producto_id' => $producto->id, 'cantidad' => 1],
                ],
                'hora_entrega'     => now()->subHours(1)->toIso8601String(), // Hora pasada
                'punto_entrega_id' => $punto->id,
                'forma_pago'       => 'efectivo',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['hora_entrega']);
    }
}

