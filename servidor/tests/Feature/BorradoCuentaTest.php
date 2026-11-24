<?php

namespace Tests\Feature;

use App\Mail\CodigoBorradoMailable;
use App\Models\Categoria;
use App\Models\CodigoAcceso;
use App\Models\Pedido;
use App\Models\PuntoEntrega;
use App\Models\Tienda;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BorradoCuentaTest extends TestCase
{
    use DatabaseTransactions;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function crearUsuario(array $atributos = []): Usuario
    {
        return Usuario::create(array_merge([
            'correo'  => uniqid('borrado_', true) . '@est.utb.edu.ec',
            'nombres' => 'Usuario Prueba',
            'estado'  => 'activa',
        ], $atributos));
    }

    private function autenticar(Usuario $usuario): string
    {
        return $usuario->createToken('test')->plainTextToken;
    }

    private function insertarCodigoBorrado(Usuario $usuario, string $codigo, int $minutosExpira = 15): CodigoAcceso
    {
        return CodigoAcceso::create([
            'correo'      => $usuario->correo,
            'codigo_hash' => Hash::make($codigo),
            'proposito'   => 'borrado',
            'expira_en'   => now()->addMinutes($minutosExpira),
            'intentos'    => 0,
            'usado_en'    => null,
        ]);
    }

    // -------------------------------------------------------------------------
    // Tests
    // -------------------------------------------------------------------------

    /** @test */
    public function test_solicitar_codigo_borrado_envia_email(): void
    {
        Mail::fake();

        $usuario = $this->crearUsuario();
        $token   = $this->autenticar($usuario);

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->postJson('/api/v1/yo/codigo-borrado');

        $response->assertStatus(204);
        Mail::assertSent(CodigoBorradoMailable::class, function ($mail) use ($usuario) {
            return $mail->hasTo($usuario->correo);
        });
    }

    /** @test */
    public function test_borrar_cuenta_sin_token_retorna_401(): void
    {
        $response = $this->deleteJson('/api/v1/yo', ['codigo' => '123456']);
        $response->assertStatus(401);
    }

    /** @test */
    public function test_borrar_cuenta_con_codigo_correcto(): void
    {
        $usuario = $this->crearUsuario();
        $token   = $this->autenticar($usuario);
        $codigo  = '445566';

        $this->insertarCodigoBorrado($usuario, $codigo);

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->deleteJson('/api/v1/yo', ['codigo' => $codigo]);

        $response->assertStatus(204);

        // Usuario anonimizado en la BD
        $usuarioActualizado = Usuario::find($usuario->id);
        $this->assertEquals('Usuario eliminado', $usuarioActualizado->nombres);
        $this->assertStringContainsString('eliminado_', $usuarioActualizado->correo);
        $this->assertNull($usuarioActualizado->whatsapp);

        // Tokens revocados — GET /yo debe retornar 401
        $this->app['auth']->forgetGuards();
        $response2 = $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/v1/yo');
        $response2->assertStatus(401);
    }

    /** @test */
    public function test_borrar_cuenta_codigo_incorrecto_falla(): void
    {
        $usuario = $this->crearUsuario();
        $token   = $this->autenticar($usuario);

        $this->insertarCodigoBorrado($usuario, '111111');

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->deleteJson('/api/v1/yo', ['codigo' => '999999']);

        $response->assertStatus(422);

        // El usuario NO fue anonimizado
        $usuarioActualizado = Usuario::find($usuario->id);
        $this->assertNotEquals('Usuario eliminado', $usuarioActualizado->nombres);
    }

    /** @test */
    public function test_borrar_cuenta_codigo_expirado_falla(): void
    {
        $usuario = $this->crearUsuario();
        $token   = $this->autenticar($usuario);
        $codigo  = '654321';

        // Código ya expirado (hace 1 minuto)
        $this->insertarCodigoBorrado($usuario, $codigo, -1);

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->deleteJson('/api/v1/yo', ['codigo' => $codigo]);

        $response->assertStatus(422);
    }

    /** @test */
    public function test_borrar_cuenta_cancela_pedidos_pendientes_de_tienda(): void
    {
        // Preparar vendedor con tienda y pedido pendiente
        $vendedor = $this->crearUsuario();
        $token    = $this->autenticar($vendedor);

        $cat   = Categoria::first();
        $punto = PuntoEntrega::first();

        $tienda = Tienda::create([
            'usuario_id'     => $vendedor->id,
            'nombre'         => 'Tienda del vendedor',
            'slug'           => 'tienda-del-vendedor-' . uniqid(),
            'estado'         => 'activa',
            'plan'           => 'gratis',
            'categoria_1_id' => $cat->id,
        ]);

        // Comprador
        $comprador = $this->crearUsuario();

        $pedido = Pedido::create([
            'tienda_id'       => $tienda->id,
            'comprador_id'    => $comprador->id,
            'codigo'          => 'PED-TEST-' . uniqid(),
            'estado'          => 'pendiente',
            'total'           => 5.00,
            'punto_entrega_id' => $punto->id,
            'hora_entrega'    => now()->addHour(),
            'forma_pago'      => 'efectivo',
        ]);

        // Insertar código y borrar cuenta del vendedor
        $codigo = '778899';
        $this->insertarCodigoBorrado($vendedor, $codigo);

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->deleteJson('/api/v1/yo', ['codigo' => $codigo])
            ->assertStatus(204);

        // El pedido debe estar cancelado por sistema
        $pedidoActualizado = Pedido::find($pedido->id);
        $this->assertEquals('cancelado', $pedidoActualizado->estado);
        $this->assertEquals('sistema', $pedidoActualizado->cancelado_por);

        // La tienda debe haber sido dada de baja y anonimizada (porque tiene pedidos vinculados)
        $tiendaActualizada = Tienda::find($tienda->id);
        $this->assertNotNull($tiendaActualizada);
        $this->assertEquals('dada_de_baja', $tiendaActualizada->estado);
        $this->assertEquals('Tienda eliminada', $tiendaActualizada->nombre);
    }

    /** @test */
    public function test_borrar_cuenta_tienda_sin_pedidos_se_elimina_completamente(): void
    {
        $vendedor = $this->crearUsuario();
        $token    = $this->autenticar($vendedor);

        $cat = Categoria::first();

        $tienda = Tienda::create([
            'usuario_id'     => $vendedor->id,
            'nombre'         => 'Tienda sin pedidos',
            'slug'           => 'tienda-sin-pedidos-' . uniqid(),
            'estado'         => 'activa',
            'plan'           => 'gratis',
            'categoria_1_id' => $cat->id,
        ]);

        $codigo = '123987';
        $this->insertarCodigoBorrado($vendedor, $codigo);

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->deleteJson('/api/v1/yo', ['codigo' => $codigo])
            ->assertStatus(204);

        $this->assertNull(Tienda::find($tienda->id));
    }

    /** @test */
    public function test_borrar_cuenta_sin_codigo_retorna_422(): void
    {
        $usuario = $this->crearUsuario();
        $token   = $this->autenticar($usuario);

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->deleteJson('/api/v1/yo', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['codigo']);
    }
}
