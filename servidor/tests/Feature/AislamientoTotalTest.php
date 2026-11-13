<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\PuntoEntrega;
use App\Models\Tienda;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AislamientoTotalTest extends TestCase
{
    use DatabaseTransactions;

    private function crearUsuario(): Usuario
    {
        return Usuario::create([
            'correo'  => uniqid('aisla_', true) . '@est.utb.edu.ec',
            'nombres' => 'Usuario Aislamiento',
            'estado'  => 'activa',
        ]);
    }

    private function crearTienda(Usuario $usuario): Tienda
    {
        $cat = Categoria::first();
        return Tienda::create([
            'usuario_id'     => $usuario->id,
            'nombre'         => 'Tienda ' . uniqid(),
            'slug'           => 'tienda-' . uniqid(),
            'estado'         => 'activa',
            'plan'           => 'gratis',
            'categoria_1_id' => $cat->id,
        ]);
    }

    private function crearProducto(Tienda $tienda): Producto
    {
        return Producto::create([
            'tienda_id'   => $tienda->id,
            'nombre'      => 'Producto ' . uniqid(),
            'descripcion' => 'Descripción prueba',
            'precio'      => 4.50,
            'disponible'  => true,
        ]);
    }

    private function crearPedido(Tienda $tienda, Usuario $comprador): Pedido
    {
        $punto = PuntoEntrega::first();
        return Pedido::create([
            'tienda_id'        => $tienda->id,
            'comprador_id'     => $comprador->id,
            'codigo'           => 'PK-' . strtoupper(uniqid()),
            'estado'           => 'pendiente',
            'total'            => 4.50,
            'punto_entrega_id' => $punto->id,
            'hora_entrega'     => now()->addHour(),
            'forma_pago'       => 'efectivo',
        ]);
    }

    /** @test */
    public function test_usuario_sin_tienda_no_puede_ver_o_modificar_mi_tienda(): void
    {
        $usuario = $this->crearUsuario();
        $token = $usuario->createToken('test')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/v1/mi-tienda');

        $response->assertStatus(404);
    }

    /** @test */
    public function test_usuario_sin_tienda_no_puede_listar_productos_de_tienda(): void
    {
        $usuario = $this->crearUsuario();
        $token = $usuario->createToken('test')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/v1/mi-tienda/productos');

        $response->assertStatus(404);
    }

    /** @test */
    public function test_usuario_no_puede_modificar_o_borrar_producto_de_tienda_ajena(): void
    {
        $vendedor1 = $this->crearUsuario();
        $tienda1   = $this->crearTienda($vendedor1);
        $producto1 = $this->crearProducto($tienda1);

        $vendedor2 = $this->crearUsuario();
        $this->crearTienda($vendedor2);
        $token2    = $vendedor2->createToken('test')->plainTextToken;

        // Intentar actualizar producto ajeno
        $responsePut = $this->withHeaders(['Authorization' => "Bearer {$token2}"])
            ->putJson("/api/v1/mi-tienda/productos/{$producto1->id}", [
                'nombre' => 'Hackeado',
                'precio' => 1.00,
            ]);
        $responsePut->assertStatus(404);

        // Intentar borrar producto ajeno
        $responseDel = $this->withHeaders(['Authorization' => "Bearer {$token2}"])
            ->deleteJson("/api/v1/mi-tienda/productos/{$producto1->id}");
        $responseDel->assertStatus(404);
    }

    /** @test */
    public function test_usuario_no_puede_ver_pedido_de_otro_comprador(): void
    {
        $vendedor = $this->crearUsuario();
        $tienda   = $this->crearTienda($vendedor);

        $comprador1 = $this->crearUsuario();
        $pedido     = $this->crearPedido($tienda, $comprador1);

        $comprador2 = $this->crearUsuario();
        $token2     = $comprador2->createToken('test')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => "Bearer {$token2}"])
            ->getJson("/api/v1/mis-pedidos/{$pedido->codigo}");

        $response->assertStatus(404);
    }

    /** @test */
    public function test_usuario_no_puede_cancelar_pedido_de_otro_comprador(): void
    {
        $vendedor = $this->crearUsuario();
        $tienda   = $this->crearTienda($vendedor);

        $comprador1 = $this->crearUsuario();
        $pedido     = $this->crearPedido($tienda, $comprador1);

        $comprador2 = $this->crearUsuario();
        $token2     = $comprador2->createToken('test')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => "Bearer {$token2}"])
            ->deleteJson("/api/v1/mis-pedidos/{$pedido->codigo}");

        $response->assertStatus(404);
    }

    /** @test */
    public function test_vendedor_no_puede_entregar_pedido_de_otra_tienda(): void
    {
        $vendedor1 = $this->crearUsuario();
        $tienda1   = $this->crearTienda($vendedor1);
        $comprador = $this->crearUsuario();
        $pedido1   = $this->crearPedido($tienda1, $comprador);

        $vendedor2 = $this->crearUsuario();
        $this->crearTienda($vendedor2);
        $token2    = $vendedor2->createToken('test')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => "Bearer {$token2}"])
            ->putJson("/api/v1/mi-tienda/pedidos/{$pedido1->codigo}/entregado");

        $response->assertStatus(404);
    }

    /** @test */
    public function test_vendedor_no_puede_cancelar_pedido_de_otra_tienda(): void
    {
        $vendedor1 = $this->crearUsuario();
        $tienda1   = $this->crearTienda($vendedor1);
        $comprador = $this->crearUsuario();
        $pedido1   = $this->crearPedido($tienda1, $comprador);

        $vendedor2 = $this->crearUsuario();
        $this->crearTienda($vendedor2);
        $token2    = $vendedor2->createToken('test')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => "Bearer {$token2}"])
            ->putJson("/api/v1/mi-tienda/pedidos/{$pedido1->codigo}/cancelar");

        $response->assertStatus(404);
    }

    /** @test */
    public function test_usuario_sin_tienda_no_puede_consultar_estado_premium(): void
    {
        $usuario = $this->crearUsuario();
        $token   = $usuario->createToken('test')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/v1/mi-tienda/premium');

        $response->assertStatus(404);
    }
}
