<?php

namespace Tests\Feature;

use App\Models\Calificacion;
use App\Models\Categoria;
use App\Models\Pedido;
use App\Models\PuntoEntrega;
use App\Models\Tienda;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CalificacionesFase3Test extends TestCase
{
    protected Usuario $vendedor;
    protected Usuario $comprador;
    protected Usuario $ajeno;
    protected Tienda $tienda;
    protected PuntoEntrega $punto;
    protected Categoria $categoria;

    protected function setUp(): void
    {
        parent::setUp();

        $this->punto = PuntoEntrega::first() ?? PuntoEntrega::create([
            'nombre'      => 'Campus Central UTB',
            'descripcion' => 'Frente a rectoría',
            'activo'      => true,
        ]);

        $this->categoria = Categoria::first() ?? Categoria::create([
            'nombre' => 'Alimentos y Bebidas',
            'slug'   => 'alimentos-bebidas-' . uniqid(),
            'icono'  => 'fastfood',
            'orden'  => 1,
            'activa' => true,
        ]);

        $this->vendedor = Usuario::create([
            'nombres'           => 'Vendedor UTB',
            'correo'            => 'vendedor_' . uniqid() . '@utb.edu.ec',
            'password'          => Hash::make('ClaveSegura2026!'),
            'codigo_estudiante' => '1234567890',
            'facultad'          => 'Ciencias Agropecuarias',
            'carrera'           => 'Veterinaria',
            'semestre'          => 5,
            'whatsapp'          => '0999999999',
            'mostrar_whatsapp'  => true,
            'es_admin'          => false,
            'estado'            => 'activa',
        ]);

        $this->tienda = Tienda::create([
            'usuario_id'         => $this->vendedor->id,
            'nombre'             => 'Tienda de Snacks ' . uniqid(),
            'slug'               => 'tienda-snacks-' . uniqid(),
            'descripcion'        => 'Snacks caseros',
            'plan'               => 'gratis',
            'estado'             => 'activa',
            'categoria_1_id'     => $this->categoria->id,
            'punto_entrega_id'   => $this->punto->id,
            'dias_atencion'      => 'lunes,martes,miercoles',
            'hora_apertura'      => '08:00',
            'hora_cierre'        => '18:00',
            'recibiendo_pedidos' => true,
        ]);

        $this->comprador = Usuario::create([
            'nombres'           => 'Comprador UTB',
            'correo'            => 'comprador_' . uniqid() . '@utb.edu.ec',
            'password'          => Hash::make('ClaveSegura2026!'),
            'codigo_estudiante' => '0987654321',
            'facultad'          => 'Administración',
            'carrera'           => 'Contabilidad',
            'semestre'          => 4,
            'whatsapp'          => '0988888888',
            'mostrar_whatsapp'  => true,
            'es_admin'          => false,
            'estado'            => 'activa',
        ]);

        $this->ajeno = Usuario::create([
            'nombres'           => 'Usuario Ajeno UTB',
            'correo'            => 'ajeno_' . uniqid() . '@utb.edu.ec',
            'password'          => Hash::make('ClaveSegura2026!'),
            'codigo_estudiante' => '1122334455',
            'facultad'          => 'Salud',
            'carrera'           => 'Enfermería',
            'semestre'          => 2,
            'whatsapp'          => '0977777777',
            'mostrar_whatsapp'  => true,
            'es_admin'          => false,
            'estado'            => 'activa',
        ]);
    }

    private function crearPedidoEntregado(int $diasDesdeEntrega = 0): Pedido
    {
        return Pedido::create([
            'codigo'             => strtoupper(Str::random(8)),
            'tienda_id'          => $this->tienda->id,
            'comprador_id'       => $this->comprador->id,
            'punto_entrega_id'   => $this->punto->id,
            'total'              => 5.00,
            'forma_pago'         => 'efectivo',
            'estado'             => 'entregado',
            'estado_cambiado_en' => now()->subDays($diasDesdeEntrega),
            'hora_entrega'       => now()->format('Y-m-d H:i:s'),
            'codigo_entrega'     => '1234',
        ]);
    }

    public function test_comprador_califica_tienda_exitosamente_en_plazo(): void
    {
        $pedido = $this->crearPedidoEntregado(1);

        Sanctum::actingAs($this->comprador);

        $response = $this->postJson("/api/v1/mis-pedidos/{$pedido->codigo}/calificacion", [
            'estrellas' => 5,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Gracias. Tu calificación se verá cuando la otra parte también califique o en 3 días.',
            ]);

        $this->assertDatabaseHas('calificaciones', [
            'pedido_id' => $pedido->id,
            'direccion' => 'a_tienda',
            'autor_id'  => $this->comprador->id,
            'estrellas' => 5,
        ]);
    }

    public function test_vendedor_califica_comprador_exitosamente_en_plazo(): void
    {
        $pedido = $this->crearPedidoEntregado(1);

        Sanctum::actingAs($this->vendedor);

        $response = $this->postJson("/api/v1/mi-tienda/pedidos/{$pedido->codigo}/calificacion", [
            'estrellas' => 4,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Gracias. Tu calificación se verá cuando la otra parte también califique o en 3 días.',
            ]);

        $this->assertDatabaseHas('calificaciones', [
            'pedido_id' => $pedido->id,
            'direccion' => 'a_comprador',
            'autor_id'  => $this->vendedor->id,
            'estrellas' => 4,
        ]);
    }

    public function test_calificar_pedido_no_entregado_retorna_422(): void
    {
        $pedido = Pedido::create([
            'codigo'             => strtoupper(Str::random(8)),
            'tienda_id'          => $this->tienda->id,
            'comprador_id'       => $this->comprador->id,
            'punto_entrega_id'   => $this->punto->id,
            'total'              => 5.00,
            'forma_pago'         => 'efectivo',
            'estado'             => 'pendiente',
            'estado_cambiado_en' => now(),
            'hora_entrega'       => now()->format('Y-m-d H:i:s'),
            'codigo_entrega'     => '1234',
        ]);

        Sanctum::actingAs($this->comprador);

        $response = $this->postJson("/api/v1/mis-pedidos/{$pedido->codigo}/calificacion", [
            'estrellas' => 5,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Este pedido ya no se puede calificar.',
            ]);
    }

    public function test_calificar_fuera_de_plazo_retorna_422(): void
    {
        // Entregado hace 4 días (> 3 días)
        $pedido = $this->crearPedidoEntregado(4);

        Sanctum::actingAs($this->comprador);

        $response = $this->postJson("/api/v1/mis-pedidos/{$pedido->codigo}/calificacion", [
            'estrellas' => 5,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Este pedido ya no se puede calificar.',
            ]);
    }

    public function test_doble_calificacion_retorna_422(): void
    {
        $pedido = $this->crearPedidoEntregado(1);

        Sanctum::actingAs($this->comprador);

        $this->postJson("/api/v1/mis-pedidos/{$pedido->codigo}/calificacion", [
            'estrellas' => 5,
        ])->assertStatus(201);

        // Intento 2
        $response = $this->postJson("/api/v1/mis-pedidos/{$pedido->codigo}/calificacion", [
            'estrellas' => 4,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Este pedido ya no se puede calificar.',
            ]);
    }

    public function test_calificaciones_a_ciegas_permanecen_ocultas_hasta_ambas_o_3_dias(): void
    {
        $pedido = $this->crearPedidoEntregado(1); // Entregado ayer

        // Comprador califica
        Calificacion::create([
            'pedido_id' => $pedido->id,
            'direccion' => 'a_tienda',
            'autor_id'  => $this->comprador->id,
            'estrellas' => 5,
            'creada_en' => now(),
        ]);

        // Vendedor consulta pedidos: la calificacion recibida NO es visible aún (es null)
        Sanctum::actingAs($this->vendedor);
        $response = $this->getJson('/api/v1/mi-tienda/pedidos');
        $response->assertStatus(200);
        $data = $response->json();
        $pedidoData = collect($data['data'] ?? $data['pedidos'] ?? $data)->firstWhere('codigo', $pedido->codigo);
        $this->assertNull($pedidoData['calificacion_recibida']);

        // Ahora vendedor también califica
        $this->postJson("/api/v1/mi-tienda/pedidos/{$pedido->codigo}/calificacion", [
            'estrellas' => 4,
        ])->assertStatus(201);

        // Ahora ambas son visibles
        $response2 = $this->getJson('/api/v1/mi-tienda/pedidos');
        $data2 = $response2->json();
        $pedidoData2 = collect($data2['data'] ?? $data2['pedidos'] ?? $data2)->firstWhere('codigo', $pedido->codigo);
        $this->assertEquals(5, $pedidoData2['calificacion_recibida']);
    }

    public function test_promedio_tienda_solo_visible_a_partir_de_3_calificaciones(): void
    {
        // Pedido 1
        $p1 = $this->crearPedidoEntregado(4); // > 3 dias, visible
        Calificacion::create(['pedido_id' => $p1->id, 'direccion' => 'a_tienda', 'autor_id' => $this->comprador->id, 'estrellas' => 5, 'creada_en' => now()]);

        // Pedido 2
        $p2 = $this->crearPedidoEntregado(4);
        Calificacion::create(['pedido_id' => $p2->id, 'direccion' => 'a_tienda', 'autor_id' => $this->comprador->id, 'estrellas' => 4, 'creada_en' => now()]);

        // Con 2 calificaciones visibles, se calcula promedio (5 + 4) / 2 = 4.5
        $response = $this->getJson("/api/v1/tiendas/{$this->tienda->slug}");
        $response->assertStatus(200);
        $tiendaData = $response->json('tienda') ?? $response->json('data') ?? $response->json();
        $this->assertEquals(4.5, $tiendaData['estrellas_promedio']);
        $this->assertEquals(2, $tiendaData['estrellas_total']);

        // Pedido 3
        $p3 = $this->crearPedidoEntregado(4);
        Calificacion::create(['pedido_id' => $p3->id, 'direccion' => 'a_tienda', 'autor_id' => $this->comprador->id, 'estrellas' => 5, 'creada_en' => now()]);

        // Con 3 calificaciones: promedio (5 + 4 + 5) / 3 = 4.7
        $response3 = $this->getJson("/api/v1/tiendas/{$this->tienda->slug}");
        $response3->assertStatus(200);
        $tiendaData3 = $response3->json('tienda') ?? $response3->json('data') ?? $response3->json();
        $this->assertEquals(4.7, $tiendaData3['estrellas_promedio']);
        $this->assertEquals(3, $tiendaData3['estrellas_total']);
    }

    public function test_usuario_ajeno_recibe_404_o_403_al_intentar_calificar(): void
    {
        $pedido = $this->crearPedidoEntregado(1);

        Sanctum::actingAs($this->ajeno);

        // Intenta calificar como comprador del pedido
        $response = $this->postJson("/api/v1/mis-pedidos/{$pedido->codigo}/calificacion", [
            'estrellas' => 5,
        ]);
        $this->assertContains($response->status(), [403, 404]);

        // Intenta calificar como vendedor del pedido (no tiene tienda o no es su tienda)
        $response2 = $this->postJson("/api/v1/mi-tienda/pedidos/{$pedido->codigo}/calificacion", [
            'estrellas' => 5,
        ]);
        $this->assertContains($response2->status(), [403, 404]);
    }

    public function test_borrado_cuenta_elimina_calificaciones_recibidas_y_anonimiza_dadas(): void
    {
        $pedido = $this->crearPedidoEntregado(1);

        // Comprador califica a la tienda
        $calTienda = Calificacion::create([
            'pedido_id' => $pedido->id,
            'direccion' => 'a_tienda',
            'autor_id'  => $this->comprador->id,
            'estrellas' => 5,
            'creada_en' => now(),
        ]);

        // Vendedor califica al comprador
        $calComprador = Calificacion::create([
            'pedido_id' => $pedido->id,
            'direccion' => 'a_comprador',
            'autor_id'  => $this->vendedor->id,
            'estrellas' => 4,
            'creada_en' => now(),
        ]);

        // Simular borrado de cuenta del comprador via API /yo
        Sanctum::actingAs($this->comprador);

        // Insertar código de acceso con propósito borrado
        \App\Models\CodigoAcceso::create([
            'correo'      => $this->comprador->correo,
            'codigo_hash' => Hash::make('654321'),
            'proposito'   => 'borrado',
            'expira_en'   => now()->addMinutes(15),
            'intentos'    => 0,
        ]);

        $response = $this->deleteJson('/api/v1/yo', [
            'codigo' => '654321',
        ]);
        $response->assertStatus(204);

        // La calificación que recibió el comprador (a_comprador) debe haberse eliminado
        $this->assertDatabaseMissing('calificaciones', [
            'id' => $calComprador->id,
        ]);

        // La calificación que dio el comprador (a_tienda) se conserva pero con autor_id = null
        $this->assertDatabaseHas('calificaciones', [
            'id'       => $calTienda->id,
            'autor_id' => null,
        ]);
    }
}
