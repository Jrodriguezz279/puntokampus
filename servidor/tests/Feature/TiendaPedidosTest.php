<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Pedido;
use App\Models\PuntoEntrega;
use App\Models\Tienda;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TiendaPedidosTest extends TestCase
{
    use DatabaseTransactions;

    protected function crearVendedorConTienda(): array
    {
        $vendedor = Usuario::create([
            'correo'               => 'vendedor_' . uniqid() . '@utb.edu.ec',
            'nombres'              => 'Vendedor Tienda',
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

        $token = $vendedor->createToken('test')->plainTextToken;

        return [$vendedor, $tienda, $token];
    }

    protected function crearPedido(Tienda $tienda, string $estado = 'pendiente'): Pedido
    {
        $comprador = Usuario::create([
            'correo'               => 'comprador_' . uniqid() . '@utb.edu.ec',
            'nombres'              => 'Comprador Test',
            'estado'               => 'activa',
            'acepto_privacidad_en' => now(),
            'whatsapp'             => '593998765432',
        ]);

        $punto = PuntoEntrega::first();

        $pedido = Pedido::create([
            'codigo'           => 'PK-TMP-' . uniqid(),
            'comprador_id'     => $comprador->id,
            'tienda_id'        => $tienda->id,
            'punto_entrega_id' => $punto->id,
            'hora_entrega'     => now()->addHours(2),
            'forma_pago'       => 'efectivo',
            'total'            => 10.00,
            'estado'           => $estado,
        ]);

        $codigo = 'PK-' . str_pad((string) $pedido->id, 6, '0', STR_PAD_LEFT);
        $pedido->update(['codigo' => $codigo]);

        return $pedido;
    }

    public function test_vendedor_lista_pedidos_de_su_tienda_con_filtro_de_estado(): void
    {
        [$vendedor, $tienda, $token] = $this->crearVendedorConTienda();
        $pedidoPendiente = $this->crearPedido($tienda, 'pendiente');
        $pedidoEntregado = $this->crearPedido($tienda, 'entregado');

        // Sin filtro: ambos pedidos
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/mi-tienda/pedidos');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));

        // Con filtro: solo pendientes
        $responseFiltro = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/mi-tienda/pedidos?estado=pendiente');

        $responseFiltro->assertStatus(200);
        $this->assertCount(1, $responseFiltro->json('data'));
        $this->assertEquals($pedidoPendiente->codigo, $responseFiltro->json('data.0.codigo'));
    }

    public function test_r17_vendedor_marca_pedido_como_entregado(): void
    {
        [$vendedor, $tienda, $token] = $this->crearVendedorConTienda();
        $pedido = $this->crearPedido($tienda, 'pendiente');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/v1/mi-tienda/pedidos/' . $pedido->codigo . '/entregado');

        $response->assertStatus(200)
            ->assertJsonPath('data.estado', 'entregado');

        $this->assertDatabaseHas('pedidos', [
            'id'     => $pedido->id,
            'estado' => 'entregado',
        ]);
    }

    public function test_r17_vendedor_cancela_pedido_pendiente(): void
    {
        [$vendedor, $tienda, $token] = $this->crearVendedorConTienda();
        $pedido = $this->crearPedido($tienda, 'pendiente');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/v1/mi-tienda/pedidos/' . $pedido->codigo . '/cancelar');

        $response->assertStatus(200)
            ->assertJsonPath('data.estado', 'cancelado')
            ->assertJsonPath('data.cancelado_por', 'vendedor');

        $this->assertDatabaseHas('pedidos', [
            'id'            => $pedido->id,
            'estado'        => 'cancelado',
            'cancelado_por' => 'vendedor',
        ]);
    }

    public function test_vendedor_intenta_entregar_pedido_ya_cancelado_falla(): void
    {
        [$vendedor, $tienda, $token] = $this->crearVendedorConTienda();
        $pedido = $this->crearPedido($tienda, 'cancelado');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/v1/mi-tienda/pedidos/' . $pedido->codigo . '/entregado');

        $response->assertStatus(422)
            ->assertJson([
                'message' => "No se puede marcar como entregado un pedido en estado 'cancelado'.",
            ]);
    }

    public function test_usuario_sin_tienda_obtiene_404_al_consultar_pedidos_de_tienda(): void
    {
        $usuarioSinTienda = Usuario::create([
            'correo'               => 'sin_tienda_' . uniqid() . '@utb.edu.ec',
            'nombres'              => 'Sin Tienda',
            'estado'               => 'activa',
            'acepto_privacidad_en' => now(),
        ]);
        $token = $usuarioSinTienda->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/mi-tienda/pedidos');

        $response->assertStatus(404);
    }
}

