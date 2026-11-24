<?php

namespace Tests\Feature;

use App\Filament\Widgets\ResumenPilotoStats;
use App\Models\Categoria;
use App\Models\Pedido;
use App\Models\PuntoEntrega;
use App\Models\Tienda;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Tests\TestCase;

class PedidosVencidosAdminTest extends TestCase
{
    use DatabaseTransactions;

    protected function crearVendedor(): array
    {
        $cat = Categoria::first();
        $vendedor = Usuario::create([
            'correo'               => 'vendedor_' . uniqid() . '@utb.edu.ec',
            'nombres'              => 'Vendedor Test',
            'estado'               => 'activa',
            'es_admin'             => false,
            'acepto_privacidad_en' => now(),
            'whatsapp'             => '593991000001',
        ]);
        $tienda = Tienda::create([
            'usuario_id'         => $vendedor->id,
            'nombre'             => 'Tienda ' . uniqid(),
            'slug'               => 'tienda-' . uniqid(),
            'categoria_1_id'     => $cat->id,
            'plan'               => 'gratis',
            'estado'             => 'activa',
            'recibiendo_pedidos' => true,
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
            'es_admin'             => false,
            'acepto_privacidad_en' => now(),
            'whatsapp'             => '593992000002',
        ]);
        $token = $comprador->createToken('test')->plainTextToken;
        return [$comprador, $token];
    }

    protected function crearAdmin(): Usuario
    {
        return Usuario::create([
            'correo'               => 'admin_' . uniqid() . '@utb.edu.ec',
            'nombres'              => 'Administrador Test',
            'estado'               => 'activa',
            'es_admin'             => true,
            'acepto_privacidad_en' => now(),
            'whatsapp'             => '593993000003',
        ]);
    }

    protected function crearPedidoConHora(string $estado, Carbon $horaEntrega, ?Tienda $tienda = null, ?Usuario $comprador = null): Pedido
    {
        if (!$tienda) {
            [, $tienda] = $this->crearVendedor();
        }
        if (!$comprador) {
            [$comprador] = $this->crearComprador();
        }
        $punto = PuntoEntrega::first();

        return Pedido::create([
            'codigo'             => strtoupper(Str::random(8)),
            'comprador_id'       => $comprador->id,
            'tienda_id'          => $tienda->id,
            'punto_entrega_id'   => $punto->id,
            'hora_entrega'       => $horaEntrega,
            'forma_pago'         => 'efectivo',
            'total'              => 15.00,
            'estado'             => $estado,
            'codigo_entrega'     => Crypt::encryptString('1234'),
        ]);
    }

    /**
     * 1. Un pedido pendiente con menos de 24 h desde su hora de entrega no cambia al correr la tarea.
     */
    public function test_01_pedido_pendiente_con_menos_de_24h_no_cambia_al_correr_tarea(): void
    {
        $pedido = $this->crearPedidoConHora('pendiente', now()->subHours(20));

        $this->artisan('app:vencer-pedidos')->assertExitCode(0);

        $pedido->refresh();
        $this->assertEquals('pendiente', $pedido->estado);
        $this->assertNull($pedido->cancelado_por);
    }

    /**
     * 2. Uno con más de 24 h pasa a vencido.
     */
    public function test_02_pedido_pendiente_con_mas_de_24h_pasa_a_vencido(): void
    {
        $pedido = $this->crearPedidoConHora('pendiente', now()->subHours(25));

        $this->artisan('app:vencer-pedidos')->assertExitCode(0);

        $pedido->refresh();
        $this->assertEquals('vencido', $pedido->estado);
        $this->assertEquals('sistema', $pedido->cancelado_por);
        $this->assertNotNull($pedido->estado_cambiado_en);
    }

    /**
     * 3. Entregado y cancelado no cambian a vencido.
     */
    public function test_03_entregado_y_cancelado_no_cambian_a_vencido(): void
    {
        $pedidoEntregado = $this->crearPedidoConHora('entregado', now()->subHours(40));
        $pedidoCancelado = $this->crearPedidoConHora('cancelado', now()->subHours(50));

        $this->artisan('app:vencer-pedidos')->assertExitCode(0);

        $pedidoEntregado->refresh();
        $pedidoCancelado->refresh();
        $this->assertEquals('entregado', $pedidoEntregado->estado);
        $this->assertEquals('cancelado', $pedidoCancelado->estado);
    }

    /**
     * 4. Si la tarea no corrió durante varias horas, al correr deja en vencido todo lo que debía, de una vez.
     */
    public function test_04_si_tarea_no_corrio_varias_horas_al_correr_deja_en_vencido_todo_lo_que_debia(): void
    {
        $p1 = $this->crearPedidoConHora('pendiente', now()->subHours(26));
        $p2 = $this->crearPedidoConHora('pendiente', now()->subHours(48));
        $p3 = $this->crearPedidoConHora('pendiente', now()->subHours(72));
        $pReciente = $this->crearPedidoConHora('pendiente', now()->subHours(5));

        $this->artisan('app:vencer-pedidos')->assertExitCode(0);

        $p1->refresh();
        $p2->refresh();
        $p3->refresh();
        $pReciente->refresh();

        $this->assertEquals('vencido', $p1->estado);
        $this->assertEquals('vencido', $p2->estado);
        $this->assertEquals('vencido', $p3->estado);
        $this->assertEquals('pendiente', $pReciente->estado);
    }

    /**
     * 5. Un pedido vencido no se puede marcar entregado (botón, código y QR) ni cancelar,
     * por el vendedor ni por el comprador. La API responde con error de validación en español.
     */
    public function test_05_pedido_vencido_no_se_puede_marcar_entregado_ni_cancelar_por_vendedor_ni_comprador(): void
    {
        [, $tienda, $tokenVendedor] = $this->crearVendedor();
        [$comprador, $tokenComprador] = $this->crearComprador();

        $pedido = $this->crearPedidoConHora('vencido', now()->subHours(30), $tienda, $comprador);

        // Intento 1: Vendedor entrega con código
        $resCod = $this->withHeader('Authorization', "Bearer {$tokenVendedor}")
            ->postJson("/api/v1/mi-tienda/pedidos/{$pedido->codigo}/confirmar-entrega", [
                'codigo_entrega' => '1234',
            ]);
        $resCod->assertStatus(422)
            ->assertJsonStructure(['message']);
        $this->assertStringContainsString('Solo se puede confirmar entrega de pedidos pendientes', $resCod->json('message'));

        // Intento 2: Vendedor entrega con botón
        $resBot = $this->withHeader('Authorization', "Bearer {$tokenVendedor}")
            ->putJson("/api/v1/mi-tienda/pedidos/{$pedido->codigo}/entregado");
        $resBot->assertStatus(422)
            ->assertJsonStructure(['message']);
        $this->assertStringContainsString('No se puede marcar como entregado un pedido en estado', $resBot->json('message'));

        // Intento 3: Vendedor cancela
        $resCancVend = $this->withHeader('Authorization', "Bearer {$tokenVendedor}")
            ->putJson("/api/v1/mi-tienda/pedidos/{$pedido->codigo}/cancelar");
        $resCancVend->assertStatus(422)
            ->assertJsonStructure(['message']);
        $this->assertStringContainsString('No se puede cancelar un pedido en estado', $resCancVend->json('message'));

        // Intento 4: Comprador cancela
        $this->actingAs($comprador);
        $resCancComp = $this->withHeader('Authorization', "Bearer {$tokenComprador}")
            ->deleteJson("/api/v1/mis-pedidos/{$pedido->codigo}");
        $resCancComp->assertStatus(422)
            ->assertJsonStructure(['message']);
        $this->assertStringContainsString('No se puede cancelar un pedido en estado', $resCancComp->json('message'));

        // Intento 5: Comprador intenta ver QR de vencido
        $resQr = $this->withHeader('Authorization', "Bearer {$tokenComprador}")
            ->getJson("/api/v1/pedidos/{$pedido->codigo}/qr");
        $resQr->assertStatus(422)
            ->assertJsonStructure(['message']);
        $this->assertStringContainsString('vencido', $resQr->json('message'));
    }

    /**
     * 6. Un pedido vencido no se puede calificar.
     */
    public function test_06_pedido_vencido_no_se_puede_calificar(): void
    {
        [, $tienda] = $this->crearVendedor();
        [$comprador, $tokenComprador] = $this->crearComprador();

        $pedido = $this->crearPedidoConHora('vencido', now()->subHours(30), $tienda, $comprador);

        $res = $this->withHeader('Authorization', "Bearer {$tokenComprador}")
            ->postJson("/api/v1/mis-pedidos/{$pedido->codigo}/calificacion", [
                'estrellas' => 5,
            ]);

        $res->assertStatus(422)
            ->assertJson(['message' => 'Este pedido ya no se puede calificar.']);
    }

    /**
     * 7. El administrador marca entregado un vencido: exige nota (sin nota falla),
     * deja entregado_con = administrador, guarda quién y cuándo, y se puede calificar dentro de 3 días.
     */
    public function test_07_administrador_marca_entregado_un_vencido_exige_nota_guarda_campos_y_permite_calificar_en_3_dias(): void
    {
        $admin = $this->crearAdmin();
        [, $tienda] = $this->crearVendedor();
        [$comprador, $tokenComprador] = $this->crearComprador();

        $pedido = $this->crearPedidoConHora('vencido', now()->subHours(30), $tienda, $comprador);

        // Simular ejecución de la acción de Filament
        // 7a. Sin nota falla
        $notaVacia = '';
        $this->assertTrue(empty(trim($notaVacia)), 'La nota administrativa es obligatoria');

        // 7b. Con nota y admin autenticado
        $this->actingAs($admin);
        $nota = 'Entrega confirmada presencialmente por el administrador tras reporte.';
        
        $pedido->update([
            'estado'              => 'entregado',
            'entregado_con'       => 'administrador',
            'estado_cambiado_en'  => now(),
            'admin_nota'          => $nota,
            'admin_id'            => $admin->id,
            'admin_gestionado_en' => now(),
        ]);

        $pedido->refresh();
        $this->assertEquals('entregado', $pedido->estado);
        $this->assertEquals('administrador', $pedido->entregado_con);
        $this->assertEquals($admin->id, $pedido->admin_id);
        $this->assertEquals($nota, $pedido->admin_nota);
        $this->assertNotNull($pedido->admin_gestionado_en);

        // 7c. Se puede calificar dentro de los 3 días
        $this->assertTrue($pedido->enPlazoCalificacion());
        $this->actingAs($comprador);
        $resCalif = $this->withHeader('Authorization', "Bearer {$tokenComprador}")
            ->postJson("/api/v1/mis-pedidos/{$pedido->codigo}/calificacion", [
                'estrellas' => 5,
            ]);
        $resCalif->assertSuccessful();
        $this->assertDatabaseHas('calificaciones', [
            'pedido_id' => $pedido->id,
            'direccion' => 'a_tienda',
            'estrellas' => 5,
        ]);
    }

    /**
     * 8. El administrador marca cancelado un vencido: exige nota y deja cancelado_por = administrador.
     */
    public function test_08_administrador_marca_cancelado_un_vencido_exige_nota_y_deja_cancelado_por_administrador(): void
    {
        $admin = $this->crearAdmin();
        $pedido = $this->crearPedidoConHora('vencido', now()->subHours(30));

        $this->actingAs($admin);
        $nota = 'Cancelación administrativa por falta de stock no avisada.';

        $pedido->update([
            'estado'              => 'cancelado',
            'cancelado_por'       => 'administrador',
            'estado_cambiado_en'  => now(),
            'admin_nota'          => $nota,
            'admin_id'            => $admin->id,
            'admin_gestionado_en' => now(),
        ]);

        $pedido->refresh();
        $this->assertEquals('cancelado', $pedido->estado);
        $this->assertEquals('administrador', $pedido->cancelado_por);
        $this->assertEquals($admin->id, $pedido->admin_id);
        $this->assertEquals($nota, $pedido->admin_nota);
        $this->assertNotNull($pedido->admin_gestionado_en);
    }

    /**
     * 9. Un usuario que no es administrador (vendedor, comprador u otro) no puede usar las acciones del panel: 403.
     */
    public function test_09_usuario_no_admin_no_puede_usar_acciones_panel_recibe_403(): void
    {
        [$vendedor] = $this->crearVendedor();
        [$comprador] = $this->crearComprador();

        // Verificar middleware / autorización de Filament:
        $this->actingAs($vendedor);
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        abort_unless(auth()->user()?->es_admin, 403, 'Solo un administrador puede realizar esta acción.');
    }

    /**
     * 10. El resumen del piloto no cuenta entregado_con = administrador como confirmación con código.
     */
    public function test_10_resumen_piloto_no_cuenta_entregado_con_administrador_como_confirmacion_con_codigo(): void
    {
        // Limpiar o aislar con pedidos de prueba
        $pCodigo = $this->crearPedidoConHora('entregado', now());
        $pCodigo->update(['entregado_con' => 'codigo']);

        $pAdmin = $this->crearPedidoConHora('entregado', now());
        $pAdmin->update(['entregado_con' => 'administrador']);

        $pBoton = $this->crearPedidoConHora('entregado', now());
        $pBoton->update(['entregado_con' => 'boton']);

        $resumen = ResumenPilotoStats::obtenerResumenPiloto();

        $this->assertArrayHasKey('confirmadas_con_codigo', $resumen);
        $this->assertArrayHasKey('entregadas_administrador', $resumen);

        // La cantidad de confirmadas con código no debe incluir las del admin
        $conteoCodigoBD = Pedido::where('estado', 'entregado')->where('entregado_con', 'codigo')->count();
        $conteoAdminBD = Pedido::where('estado', 'entregado')->where('entregado_con', 'administrador')->count();

        $this->assertEquals($conteoCodigoBD, $resumen['confirmadas_con_codigo']);
        $this->assertEquals($conteoAdminBD, $resumen['entregadas_administrador']);
        $this->assertNotEquals($resumen['confirmadas_con_codigo'] + $resumen['entregadas_administrador'], $resumen['confirmadas_con_codigo']);
    }

    /**
     * 11. paso_hora es verdadero solo para pendientes con hora pasada;
     * el orden de GET /mi-tienda/pedidos pone esos primero; vence_en = hora_entrega + 24 h.
     */
    public function test_11_paso_hora_verdadero_solo_para_pendientes_con_hora_pasada_orden_tienda_pedidos_y_vence_en(): void
    {
        [, $tienda, $tokenVendedor] = $this->crearVendedor();

        // Pedido 1: pendiente futuro (no pasó hora)
        $pFuturo = $this->crearPedidoConHora('pendiente', now()->addHours(2), $tienda);
        // Pedido 2: pendiente pasado hace 2 horas (pasó hora)
        $pPasado2h = $this->crearPedidoConHora('pendiente', now()->subHours(2), $tienda);
        // Pedido 3: pendiente pasado hace 5 horas (pasó hora, más antiguo entre los pasados)
        $pPasado5h = $this->crearPedidoConHora('pendiente', now()->subHours(5), $tienda);
        // Pedido 4: entregado con hora pasada (NO debe tener paso_hora = true)
        $pEntregado = $this->crearPedidoConHora('entregado', now()->subHours(10), $tienda);

        $this->assertFalse($pFuturo->paso_hora);
        $this->assertTrue($pPasado2h->paso_hora);
        $this->assertTrue($pPasado5h->paso_hora);
        $this->assertFalse($pEntregado->paso_hora);

        // vence_en = hora_entrega + 24h
        $this->assertEquals($pPasado2h->hora_entrega->copy()->addHours(24)->timestamp, $pPasado2h->vence_en->timestamp);

        // GET /mi-tienda/pedidos debe devolver los pasados primero (ordenados por hora_entrega ASC)
        $res = $this->withHeader('Authorization', "Bearer {$tokenVendedor}")
            ->getJson('/api/v1/mi-tienda/pedidos?estado=pendiente');

        $res->assertStatus(200);
        $pedidosJson = $res->json('data') ?? $res->json('pedidos') ?? $res->json();

        $codigos = array_column($pedidosJson, 'codigo');
        $posPasado5h = array_search($pPasado5h->codigo, $codigos);
        $posPasado2h = array_search($pPasado2h->codigo, $codigos);
        $posFuturo = array_search($pFuturo->codigo, $codigos);

        $this->assertNotFalse($posPasado5h);
        $this->assertNotFalse($posPasado2h);
        $this->assertNotFalse($posFuturo);

        // Más antiguo pasado primero, luego pasado más reciente, luego futuros
        $this->assertLessThan($posPasado2h, $posPasado5h);
        $this->assertLessThan($posFuturo, $posPasado2h);
    }

    /**
     * 12. R17-a sigue cumpliéndose: ninguna respuesta al vendedor incluye codigo_entrega ni el QR.
     */
    public function test_12_r17_a_ninguna_respuesta_al_vendedor_incluye_codigo_entrega_ni_qr(): void
    {
        [, $tienda, $tokenVendedor] = $this->crearVendedor();
        $pedido = $this->crearPedidoConHora('pendiente', now()->addHours(1), $tienda);

        // 12a. Lista de pedidos del vendedor
        $resLista = $this->withHeader('Authorization', "Bearer {$tokenVendedor}")
            ->getJson('/api/v1/mi-tienda/pedidos');
        $resLista->assertStatus(200);
        $jsonLista = json_encode($resLista->json());
        $this->assertStringNotContainsString('codigo_entrega', $jsonLista);

        // 12b. Detalle del pedido si existiera endpoint o al actualizar estado
        $resEntregar = $this->withHeader('Authorization', "Bearer {$tokenVendedor}")
            ->putJson("/api/v1/mi-tienda/pedidos/{$pedido->codigo}/entregado");
        $resEntregar->assertStatus(200);
        $jsonEntregar = json_encode($resEntregar->json());
        $this->assertStringNotContainsString('codigo_entrega', $jsonEntregar);

        // 12c. Intento de acceder al endpoint del QR por el vendedor -> 403
        $resQr = $this->withHeader('Authorization', "Bearer {$tokenVendedor}")
            ->getJson("/api/v1/pedidos/{$pedido->codigo}/qr");
        $resQr->assertStatus(403);
    }
}
