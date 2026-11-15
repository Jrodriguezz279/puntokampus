<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Pedido;
use App\Models\PuntoEntrega;
use App\Models\Tienda;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

/**
 * R17: Pruebas de confirmación de entrega con código de 4 dígitos.
 * Cubre: código correcto, incorrecto, bloqueo, pedido cancelado/vencido,
 * vendedor ajeno, que el vendedor nunca recibe el código, y que el comprador
 * sí puede ver su propio código.
 */
class ConfirmacionEntregaTest extends TestCase
{
    use DatabaseTransactions;

    private string $codigoEntregaPlano = '7391';

    // ─── Helpers ─────────────────────────────────────────────────────────────

    protected function crearVendedor(): array
    {
        $cat = Categoria::first();
        $vendedor = Usuario::create([
            'correo'               => 'vendedor_' . uniqid() . '@utb.edu.ec',
            'nombres'              => 'Vendedor Test',
            'estado'               => 'activa',
            'acepto_privacidad_en' => now(),
            'whatsapp'             => '593991000001',
        ]);
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

    protected function crearComprador(): array
    {
        $comprador = Usuario::create([
            'correo'               => 'comprador_' . uniqid() . '@utb.edu.ec',
            'nombres'              => 'Comprador Test',
            'estado'               => 'activa',
            'acepto_privacidad_en' => now(),
            'whatsapp'             => '593992000002',
        ]);
        $token = $comprador->createToken('test')->plainTextToken;
        return [$comprador, $token];
    }

    protected function crearPedidoConCodigo(Tienda $tienda, int $compradorId, string $estado = 'pendiente'): Pedido
    {
        $punto = PuntoEntrega::first();
        $pedido = Pedido::create([
            'codigo'           => 'PK-TMP-' . uniqid(),
            'comprador_id'     => $compradorId,
            'tienda_id'        => $tienda->id,
            'punto_entrega_id' => $punto->id,
            'hora_entrega'     => now()->addHours(2),
            'forma_pago'       => 'efectivo',
            'total'            => 15.00,
            'estado'           => $estado,
            'codigo_entrega'   => Crypt::encryptString($this->codigoEntregaPlano),
            'intentos_codigo'  => 0,
            'codigo_bloqueado' => false,
        ]);
        $codigoFinal = 'PK-' . str_pad((string) $pedido->id, 6, '0', STR_PAD_LEFT);
        $pedido->update(['codigo' => $codigoFinal]);
        return $pedido;
    }

    // ─── Tests ───────────────────────────────────────────────────────────────

    /**
     * T1: Código correcto → pedido pasa a "entregado".
     */
    public function test_r17_confirma_entrega_con_codigo_correcto(): void
    {
        [$vendedor, $tienda, $tokenV] = $this->crearVendedor();
        [$comprador, $tokenC]         = $this->crearComprador();
        $pedido = $this->crearPedidoConCodigo($tienda, $comprador->id);

        $response = $this->withHeader('Authorization', 'Bearer ' . $tokenV)
            ->putJson("/api/v1/mi-tienda/pedidos/{$pedido->codigo}/confirmar-entrega", [
                'codigo_entrega' => $this->codigoEntregaPlano,
            ]);

        $response->assertStatus(200)
                 ->assertJsonPath('data.estado', 'entregado');

        $this->assertDatabaseHas('pedidos', [
            'id'               => $pedido->id,
            'estado'           => 'entregado',
            'intentos_codigo'  => 0,
            'codigo_bloqueado' => false,
        ]);

        // El campo codigo_entrega debe haberse limpiado
        $this->assertNull(Pedido::find($pedido->id)->getRawOriginal('codigo_entrega'));
    }

    /**
     * T2: Código incorrecto → 422 y contador incrementado.
     */
    public function test_r17_codigo_incorrecto_incrementa_intentos(): void
    {
        [$vendedor, $tienda, $tokenV] = $this->crearVendedor();
        [$comprador,]                 = $this->crearComprador();
        $pedido = $this->crearPedidoConCodigo($tienda, $comprador->id);

        $response = $this->withHeader('Authorization', 'Bearer ' . $tokenV)
            ->putJson("/api/v1/mi-tienda/pedidos/{$pedido->codigo}/confirmar-entrega", [
                'codigo_entrega' => '0000',
            ]);

        $response->assertStatus(422);

        $this->assertEquals(1, Pedido::find($pedido->id)->intentos_codigo);
        $this->assertEquals('pendiente', Pedido::find($pedido->id)->estado);
    }

    /**
     * T3: 5 intentos fallidos → pedido bloqueado (429).
     */
    public function test_r17_bloqueo_tras_cinco_intentos_fallidos(): void
    {
        [$vendedor, $tienda, $tokenV] = $this->crearVendedor();
        [$comprador,]                 = $this->crearComprador();
        $pedido = $this->crearPedidoConCodigo($tienda, $comprador->id);

        for ($i = 0; $i < 4; $i++) {
            $this->withHeader('Authorization', 'Bearer ' . $tokenV)
                ->putJson("/api/v1/mi-tienda/pedidos/{$pedido->codigo}/confirmar-entrega", [
                    'codigo_entrega' => '1111',
                ]);
        }

        // 5to intento fallido → debe bloquear
        $response = $this->withHeader('Authorization', 'Bearer ' . $tokenV)
            ->putJson("/api/v1/mi-tienda/pedidos/{$pedido->codigo}/confirmar-entrega", [
                'codigo_entrega' => '2222',
            ]);

        $response->assertStatus(429);

        $pedidoActualizado = Pedido::find($pedido->id);
        $this->assertTrue((bool) $pedidoActualizado->codigo_bloqueado);
        $this->assertEquals(5, $pedidoActualizado->intentos_codigo);

        // Intentar de nuevo con código correcto sigue dando 429
        $this->withHeader('Authorization', 'Bearer ' . $tokenV)
            ->putJson("/api/v1/mi-tienda/pedidos/{$pedido->codigo}/confirmar-entrega", [
                'codigo_entrega' => $this->codigoEntregaPlano,
            ])
            ->assertStatus(429);
    }

    /**
     * T4: Pedido cancelado → 422 "no permitido".
     */
    public function test_r17_pedido_cancelado_no_puede_confirmarse(): void
    {
        [$vendedor, $tienda, $tokenV] = $this->crearVendedor();
        [$comprador,]                 = $this->crearComprador();
        $pedido = $this->crearPedidoConCodigo($tienda, $comprador->id, 'cancelado');

        $this->withHeader('Authorization', 'Bearer ' . $tokenV)
            ->putJson("/api/v1/mi-tienda/pedidos/{$pedido->codigo}/confirmar-entrega", [
                'codigo_entrega' => $this->codigoEntregaPlano,
            ])
            ->assertStatus(422);
    }

    /**
     * T5: Pedido vencido → 422 "no permitido".
     */
    public function test_r17_pedido_vencido_no_puede_confirmarse(): void
    {
        [$vendedor, $tienda, $tokenV] = $this->crearVendedor();
        [$comprador,]                 = $this->crearComprador();
        $pedido = $this->crearPedidoConCodigo($tienda, $comprador->id, 'vencido');

        $this->withHeader('Authorization', 'Bearer ' . $tokenV)
            ->putJson("/api/v1/mi-tienda/pedidos/{$pedido->codigo}/confirmar-entrega", [
                'codigo_entrega' => $this->codigoEntregaPlano,
            ])
            ->assertStatus(422);
    }

    /**
     * T6: Vendedor de otra tienda recibe 404.
     */
    public function test_r17_vendedor_ajeno_recibe_404(): void
    {
        [, $tienda,]                      = $this->crearVendedor();
        [$comprador,]                      = $this->crearComprador();
        $pedido = $this->crearPedidoConCodigo($tienda, $comprador->id);

        // Otro vendedor con su propia tienda
        [$, $, $tokenOtro] = $this->crearVendedor();

        $this->withHeader('Authorization', 'Bearer ' . $tokenOtro)
            ->putJson("/api/v1/mi-tienda/pedidos/{$pedido->codigo}/confirmar-entrega", [
                'codigo_entrega' => $this->codigoEntregaPlano,
            ])
            ->assertStatus(404);
    }

    /**
     * T7: La respuesta del vendedor al listar pedidos NUNCA contiene codigo_entrega.
     */
    public function test_r17_respuesta_vendedor_nunca_contiene_codigo_entrega(): void
    {
        [$vendedor, $tienda, $tokenV] = $this->crearVendedor();
        [$comprador,]                 = $this->crearComprador();
        $this->crearPedidoConCodigo($tienda, $comprador->id);

        $response = $this->withHeader('Authorization', 'Bearer ' . $tokenV)
            ->getJson('/api/v1/mi-tienda/pedidos');

        $response->assertStatus(200);

        $items = $response->json('data');
        foreach ($items as $item) {
            $this->assertArrayNotHasKey('codigo_entrega', $item,
                'El vendedor NO debe recibir codigo_entrega en la respuesta.');
        }
    }

    /**
     * T8: El comprador puede ver su propio código cuando el pedido está pendiente.
     */
    public function test_r17_comprador_ve_su_propio_codigo(): void
    {
        [, $tienda,]          = $this->crearVendedor();
        [$comprador, $tokenC] = $this->crearComprador();
        $pedido = $this->crearPedidoConCodigo($tienda, $comprador->id);

        $response = $this->withHeader('Authorization', 'Bearer ' . $tokenC)
            ->getJson("/api/v1/mis-pedidos/{$pedido->codigo}");

        $response->assertStatus(200)
                 ->assertJsonPath('data.codigo_entrega', $this->codigoEntregaPlano);
    }

    /**
     * T9: El comprador NO puede ver código de pedido ajeno (404).
     */
    public function test_r17_comprador_no_ve_codigo_de_pedido_ajeno(): void
    {
        [, $tienda,]          = $this->crearVendedor();
        [$comprador1,]        = $this->crearComprador();
        [$, $tokenC2]         = $this->crearComprador();
        $pedido = $this->crearPedidoConCodigo($tienda, $comprador1->id);

        $this->withHeader('Authorization', 'Bearer ' . $tokenC2)
            ->getJson("/api/v1/mis-pedidos/{$pedido->codigo}")
            ->assertStatus(404);
    }
}
