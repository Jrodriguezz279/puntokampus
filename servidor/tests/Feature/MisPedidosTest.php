<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Pedido;
use App\Models\PuntoEntrega;
use App\Models\Tienda;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MisPedidosTest extends TestCase
{
    use DatabaseTransactions;

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

    protected function crearPedidoParaComprador(Usuario $comprador, string $estado = 'pendiente'): Pedido
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
            'nombre'         => 'Tienda ' . uniqid(),
            'slug'           => 'tienda-' . uniqid(),
            'categoria_1_id' => $cat->id,
            'plan'           => 'gratis',
            'estado'         => 'activa',
        ]);

        $punto = PuntoEntrega::first();

        $pedido = Pedido::create([
            'codigo'           => 'PK-TMP-' . uniqid(),
            'comprador_id'     => $comprador->id,
            'tienda_id'        => $tienda->id,
            'punto_entrega_id' => $punto->id,
            'hora_entrega'     => now()->addHours(2),
            'forma_pago'       => 'efectivo',
            'total'            => 5.00,
            'estado'           => $estado,
        ]);

        $codigo = 'PK-' . str_pad((string) $pedido->id, 6, '0', STR_PAD_LEFT);
        $pedido->update(['codigo' => $codigo]);

        return $pedido;
    }

    public function test_listar_mis_pedidos(): void
    {
        [$comprador, $token] = $this->crearComprador();
        $pedido = $this->crearPedidoParaComprador($comprador);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/mis-pedidos');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'codigo', 'total', 'estado'],
                ],
                'links',
                'meta',
            ]);

        $this->assertEquals($pedido->codigo, $response->json('data.0.codigo'));
    }

    public function test_ver_detalle_mi_pedido_por_codigo(): void
    {
        [$comprador, $token] = $this->crearComprador();
        $pedido = $this->crearPedidoParaComprador($comprador);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/mis-pedidos/' . $pedido->codigo);

        $response->assertStatus(200)
            ->assertJsonPath('data.codigo', $pedido->codigo);
    }

    public function test_no_puede_ver_pedido_de_otro_comprador(): void
    {
        [$comprador1] = $this->crearComprador();
        [$comprador2, $token2] = $this->crearComprador();

        $pedido1 = $this->crearPedidoParaComprador($comprador1);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token2)
            ->getJson('/api/v1/mis-pedidos/' . $pedido1->codigo);

        $response->assertStatus(404);
    }

    public function test_cancelar_pedido_pendiente_exitoso(): void
    {
        [$comprador, $token] = $this->crearComprador();
        $pedido = $this->crearPedidoParaComprador($comprador, 'pendiente');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/v1/mis-pedidos/' . $pedido->codigo);

        $response->assertStatus(200)
            ->assertJsonPath('data.estado', 'cancelado')
            ->assertJsonPath('data.cancelado_por', 'comprador');

        $this->assertDatabaseHas('pedidos', [
            'id'            => $pedido->id,
            'estado'        => 'cancelado',
            'cancelado_por' => 'comprador',
        ]);
    }

    public function test_r16_cancelar_pedido_ya_entregado_o_cancelado_falla(): void
    {
        [$comprador, $token] = $this->crearComprador();
        $pedidoEntregado = $this->crearPedidoParaComprador($comprador, 'entregado');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/v1/mis-pedidos/' . $pedidoEntregado->codigo);

        $response->assertStatus(422)
            ->assertJson([
                'message' => "No se puede cancelar un pedido en estado 'entregado'.",
            ]);
    }
}

