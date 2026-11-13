<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Pedido;
use App\Models\PuntoEntrega;
use App\Models\Reporte;
use App\Models\Tienda;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BajaTiendaReporteTest extends TestCase
{
    use DatabaseTransactions;

    public function test_dar_de_baja_tienda_cancela_pedidos_pendientes_con_cancelado_por_sistema(): void
    {
        $vendedor = Usuario::create([
            'correo'               => 'vendedor_' . uniqid() . '@utb.edu.ec',
            'nombres'              => 'Vendedor Con Pedidos',
            'estado'               => 'activa',
            'acepto_privacidad_en' => now(),
            'whatsapp'             => '593991234567',
        ]);

        $comprador = Usuario::create([
            'correo'               => 'comprador_' . uniqid() . '@utb.edu.ec',
            'nombres'              => 'Comprador Con Pedidos',
            'estado'               => 'activa',
            'acepto_privacidad_en' => now(),
        ]);

        $cat = Categoria::first();
        $tienda = Tienda::create([
            'usuario_id'     => $vendedor->id,
            'nombre'         => 'Tienda Con Pedidos ' . uniqid(),
            'slug'           => 'tienda-ped-' . uniqid(),
            'categoria_1_id' => $cat->id,
            'plan'           => 'gratis',
            'estado'         => 'activa',
        ]);

        $punto = PuntoEntrega::first();

        // Pedido pendiente de la tienda
        $pedidoPendiente = Pedido::create([
            'codigo'           => 'PK-TMP-' . uniqid(),
            'comprador_id'     => $comprador->id,
            'tienda_id'        => $tienda->id,
            'punto_entrega_id' => $punto->id,
            'hora_entrega'     => now()->addHours(2),
            'forma_pago'       => 'efectivo',
            'total'            => 10.00,
            'estado'           => 'pendiente',
        ]);

        // Pedido ya entregado de la tienda
        $pedidoEntregado = Pedido::create([
            'codigo'           => 'PK-TMP-' . uniqid(),
            'comprador_id'     => $comprador->id,
            'tienda_id'        => $tienda->id,
            'punto_entrega_id' => $punto->id,
            'hora_entrega'     => now()->subHours(5),
            'forma_pago'       => 'efectivo',
            'total'            => 10.00,
            'estado'           => 'entregado',
        ]);

        // Dar de baja la tienda con motivo (lógica H22 / R23)
        $tienda->update([
            'estado'          => 'dada_de_baja',
            'motivo_baja'     => 'Incumplimiento reiterado de entregas a estudiantes',
            'dada_de_baja_en' => now(),
        ]);

        Pedido::where('tienda_id', $tienda->id)
            ->where('estado', 'pendiente')
            ->update([
                'estado'             => 'cancelado',
                'cancelado_por'      => 'sistema',
                'estado_cambiado_en' => now(),
            ]);

        $pedidoPendiente->refresh();
        $pedidoEntregado->refresh();

        $this->assertEquals('cancelado', $pedidoPendiente->estado);
        $this->assertEquals('sistema', $pedidoPendiente->cancelado_por);
        $this->assertEquals('entregado', $pedidoEntregado->estado);
    }
}

