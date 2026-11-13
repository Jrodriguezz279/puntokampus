<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Pedido;
use App\Models\PuntoEntrega;
use App\Models\Tienda;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class VencerPedidosTest extends TestCase
{
    use DatabaseTransactions;

    protected function crearPedidoConHora(string $estado, $horaEntrega): Pedido
    {
        $vendedor = Usuario::create([
            'correo'               => 'vendedor_' . uniqid() . '@utb.edu.ec',
            'nombres'              => 'Vendedor Vencer',
            'estado'               => 'activa',
            'acepto_privacidad_en' => now(),
            'whatsapp'             => '593991234567',
        ]);

        $comprador = Usuario::create([
            'correo'               => 'comprador_' . uniqid() . '@utb.edu.ec',
            'nombres'              => 'Comprador Vencer',
            'estado'               => 'activa',
            'acepto_privacidad_en' => now(),
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
            'hora_entrega'     => $horaEntrega,
            'forma_pago'       => 'efectivo',
            'total'            => 10.00,
            'estado'           => $estado,
        ]);

        $codigo = 'PK-' . str_pad((string) $pedido->id, 6, '0', STR_PAD_LEFT);
        $pedido->update(['codigo' => $codigo]);

        return $pedido;
    }

    public function test_r18_command_vence_pedidos_pendientes_con_mas_de_24_horas_pasadas(): void
    {
        // 1. Pedido pendiente con más de 24h pasadas -> DEBE VENCER
        $pedidoVencible = $this->crearPedidoConHora('pendiente', now()->subHours(25));

        // 2. Pedido pendiente con menos de 24h pasadas (10h) -> NO DEBE VENCER
        $pedidoReciente = $this->crearPedidoConHora('pendiente', now()->subHours(10));

        // 3. Pedido ya entregado con más de 24h pasadas -> NO DEBE VENCER
        $pedidoEntregado = $this->crearPedidoConHora('entregado', now()->subHours(30));

        $this->artisan('app:vencer-pedidos')
            ->assertExitCode(0);

        $pedidoVencible->refresh();
        $pedidoReciente->refresh();
        $pedidoEntregado->refresh();

        $this->assertEquals('vencido', $pedidoVencible->estado);
        $this->assertEquals('sistema', $pedidoVencible->cancelado_por);
        $this->assertNotNull($pedidoVencible->estado_cambiado_en);

        $this->assertEquals('pendiente', $pedidoReciente->estado);
        $this->assertNull($pedidoReciente->cancelado_por);

        $this->assertEquals('entregado', $pedidoEntregado->estado);
    }
}
