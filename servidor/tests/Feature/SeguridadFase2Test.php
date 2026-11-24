<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\CodigoAcceso;
use App\Models\Pedido;
use App\Models\PuntoEntrega;
use App\Models\SolicitudPremium;
use App\Models\Tienda;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SeguridadFase2Test extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Storage::fake('local');
    }

    private function crearUsuario(string $nombre = 'Estudiante', bool $esAdmin = false): Usuario
    {
        return Usuario::create([
            'correo' => 'user_' . uniqid() . '@utb.edu.ec',
            'nombres' => $nombre,
            'password' => Hash::make('ClaveSegura2026!#123'),
            'es_admin' => $esAdmin,
            'estado' => 'activa',
            'acepto_privacidad_en' => now(),
        ]);
    }

    private function crearTienda(Usuario $vendedor): Tienda
    {
        $cat = Categoria::first() ?? Categoria::create(['nombre' => 'Snacks', 'icono' => 'cookie', 'orden' => 1]);
        $punto = PuntoEntrega::first() ?? PuntoEntrega::create(['nombre' => 'Entrada Principal', 'activo' => true]);

        return Tienda::create([
            'usuario_id' => $vendedor->id,
            'nombre' => 'Tienda ' . uniqid(),
            'slug' => 'tienda-' . uniqid(),
            'categoria_1_id' => $cat->id,
            'punto_entrega_id' => $punto->id,
            'estado' => 'activa',
            'plan' => 'gratis',
            'dias_atencion' => 'lunes,martes',
            'hora_apertura' => '08:00',
            'hora_cierre' => '18:00',
            'recibiendo_pedidos' => true,
        ]);
    }

    private function crearPedido(Tienda $tienda, Usuario $comprador): Pedido
    {
        $punto = PuntoEntrega::first() ?? PuntoEntrega::create(['nombre' => 'Entrada Principal', 'activo' => true]);

        return Pedido::create([
            'codigo' => 'PK-' . strtoupper(uniqid()),
            'tienda_id' => $tienda->id,
            'comprador_id' => $comprador->id,
            'punto_entrega_id' => $punto->id,
            'fecha_entrega' => now()->addDay()->toDateString(),
            'hora_entrega' => '10:00',
            'subtotal' => 5.00,
            'descuento' => 0.00,
            'total' => 5.00,
            'forma_pago' => 'efectivo',
            'estado' => 'pendiente',
            'codigo_entrega' => Crypt::encryptString('1234'),
        ]);
    }

    /**
     * R17-a: Solo el comprador puede ver el QR de confirmación de entrega.
     * Vendedor recibe 403, usuario ajeno recibe 403, anónimo recibe 401.
     */
    public function test_r17_a_solo_comprador_puede_ver_qr_ajeno_recibe_403_vendedor_recibe_403(): void
    {
        $vendedor = $this->crearUsuario('Carlos Vendedor');
        $tienda = $this->crearTienda($vendedor);
        $comprador = $this->crearUsuario('Maria Compradora');
        $ajeno = $this->crearUsuario('Pedro Ajeno');

        $pedido = $this->crearPedido($tienda, $comprador);

        // 1. Comprador dueño del pedido -> 200 OK
        $tokenComprador = $comprador->createToken('test')->plainTextToken;
        $this->app['auth']->forgetGuards();
        $this->withHeader('Authorization', 'Bearer ' . $tokenComprador)
            ->getJson("/api/v1/pedidos/{$pedido->codigo}/qr")
            ->assertStatus(200);

        // 2. Vendedor de la tienda del pedido -> 403 Forbidden
        $tokenVendedor = $vendedor->createToken('test')->plainTextToken;
        $this->app['auth']->forgetGuards();
        $this->withHeader('Authorization', 'Bearer ' . $tokenVendedor)
            ->getJson("/api/v1/pedidos/{$pedido->codigo}/qr")
            ->assertStatus(403);

        // 3. Usuario ajeno -> 403 Forbidden
        $tokenAjeno = $ajeno->createToken('test')->plainTextToken;
        $this->app['auth']->forgetGuards();
        $this->withHeader('Authorization', 'Bearer ' . $tokenAjeno)
            ->getJson("/api/v1/pedidos/{$pedido->codigo}/qr")
            ->assertStatus(403);

        // 4. No autenticado -> 401 Unauthorized
        $this->flushHeaders();
        $this->app['auth']->forgetGuards();
        $this->getJson("/api/v1/pedidos/{$pedido->codigo}/qr")
            ->assertStatus(401);

        // 5. En ruta Web: comprador -> 200, vendedor -> 403, ajeno -> 403
        $this->actingAs($comprador)
            ->get("/app/pedidos/{$pedido->codigo}/qr")
            ->assertStatus(200);

        $this->actingAs($vendedor)
            ->get("/app/pedidos/{$pedido->codigo}/qr")
            ->assertStatus(403);

        $this->actingAs($ajeno)
            ->get("/app/pedidos/{$pedido->codigo}/qr")
            ->assertStatus(403);
    }

    /**
     * Eliminación de código universal: '123456' es rechazado en todas partes.
     */
    public function test_codigo_universal_123456_rechazado_en_todas_las_rutas(): void
    {
        $usuario = $this->crearUsuario('Usuario Test');

        // Código aleatorio real en BD
        CodigoAcceso::create([
            'correo' => $usuario->correo,
            'codigo_hash' => Hash::make('654321'),
            'proposito' => 'acceso',
            'expira_en' => now()->addMinutes(10),
            'intentos' => 0,
        ]);

        // Intentar usar 123456 debe fallar
        $this->postJson('/api/v1/auth/verificar', [
            'correo' => $usuario->correo,
            'codigo' => '123456',
        ])->assertStatus(422)
          ->assertJsonValidationErrors(['codigo']);

        // Intentar recuperación con 123456 debe fallar
        CodigoAcceso::create([
            'correo' => $usuario->correo,
            'codigo_hash' => Hash::make('654321'),
            'proposito' => 'recuperacion',
            'expira_en' => now()->addMinutes(10),
            'intentos' => 0,
        ]);

        $this->postJson('/api/v1/auth/recuperar', [
            'correo' => $usuario->correo,
            'codigo' => '123456',
            'contrasena' => 'NuevaClaveValida2026!#',
        ])->assertStatus(422)
          ->assertJsonValidationErrors(['codigo']);
    }

    /**
     * Regla de contraseñas: mínimo 12 caracteres y máximo 128 caracteres.
     */
    public function test_contrasena_minimo_12_caracteres_rechaza_cortas(): void
    {
        $usuario = $this->crearUsuario();

        CodigoAcceso::create([
            'correo' => $usuario->correo,
            'codigo_hash' => Hash::make('998877'),
            'proposito' => 'recuperacion',
            'expira_en' => now()->addMinutes(10),
            'intentos' => 0,
        ]);

        // Contraseña de 6 caracteres -> debe ser rechazada (422)
        $this->postJson('/api/v1/auth/recuperar', [
            'correo' => $usuario->correo,
            'codigo' => '998877',
            'contrasena' => 'corta6',
        ])->assertStatus(422)
          ->assertJsonValidationErrors(['contrasena']);

        // Contraseña de 11 caracteres -> debe ser rechazada (422)
        $this->postJson('/api/v1/auth/recuperar', [
            'correo' => $usuario->correo,
            'codigo' => '998877',
            'contrasena' => 'corta11char',
        ])->assertStatus(422)
          ->assertJsonValidationErrors(['contrasena']);

        // Contraseña de 12 caracteres -> aceptada
        $this->postJson('/api/v1/auth/recuperar', [
            'correo' => $usuario->correo,
            'codigo' => '998877',
            'contrasena' => 'DoceCaracter',
        ])->assertStatus(204);
    }

    /**
     * Throttling y fuerza bruta: 5 intentos fallidos de contraseña bloquean la cuenta temporalmente.
     */
    public function test_rate_limiting_5_intentos_fallidos_bloquea_contrasena(): void
    {
        $usuario = $this->crearUsuario();
        $throttleKey = 'login_contrasena:' . $usuario->correo;
        RateLimiter::clear($throttleKey);

        for ($i = 1; $i <= 5; $i++) {
            $this->postJson('/api/v1/auth/entrar', [
                'correo' => $usuario->correo,
                'contrasena' => 'ClaveIncorrecta' . $i,
            ])->assertStatus(422)
              ->assertJsonValidationErrors(['contrasena']);
        }

        // El 6to intento debe recibir error de bloqueo por throttling
        $response = $this->postJson('/api/v1/auth/entrar', [
            'correo' => $usuario->correo,
            'contrasena' => 'ClaveIncorrecta6',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['contrasena']);

        $errorMsg = $response->json('errors.contrasena.0');
        $this->assertStringContainsString('Demasiados intentos fallidos', $errorMsg);

        RateLimiter::clear($throttleKey);
    }

    /**
     * Anti-enumeración: solicitar recuperación con correo no registrado no revela si existe o no.
     */
    public function test_anti_enumeracion_recuperacion_no_revela_si_correo_existe(): void
    {
        $correoInexistente = 'noexiste_' . uniqid() . '@utb.edu.ec';

        // La API devuelve 204 sin revelar si el usuario existe
        $this->postJson('/api/v1/auth/codigo-recuperacion', [
            'correo' => $correoInexistente,
        ])->assertStatus(204);

        // La Web redirige a ingresar código sin advertir si el usuario existe
        $this->post('/app/olvide-contrasena/codigo', [
            'correo' => $correoInexistente,
        ])->assertRedirect('/app/olvide-contrasena/recuperar');
    }

    /**
     * Ruta protegida de comprobante: solo admin o la tienda dueña pueden verlo.
     */
    public function test_comprobante_foto_solo_admin_y_duenio_pueden_verlo(): void
    {
        $vendedor = $this->crearUsuario('Vendedor Titular');
        $tienda = $this->crearTienda($vendedor);
        $admin = $this->crearUsuario('Admin Sistema', esAdmin: true);
        $ajeno = $this->crearUsuario('Usuario Ajeno');

        // Crear archivo simulado en disco local
        Storage::disk('local')->put('comprobantes/recibo_test.jpg', 'fake-image-content');

        $solicitud = SolicitudPremium::create([
            'tienda_id' => $tienda->id,
            'numero_comprobante' => 'COMP-' . uniqid(),
            'monto' => 5.00,
            'foto_comprobante' => 'comprobantes/recibo_test.jpg',
            'estado' => 'pendiente',
        ]);

        // 1. Dueño de la tienda accede -> 200 OK
        $this->actingAs($vendedor)
            ->get("/comprobantes/{$solicitud->id}/foto")
            ->assertStatus(200);

        // 2. Administrador accede -> 200 OK
        $this->actingAs($admin)
            ->get("/comprobantes/{$solicitud->id}/foto")
            ->assertStatus(200);

        // 3. Usuario ajeno -> 403 Forbidden
        $this->actingAs($ajeno)
            ->get("/comprobantes/{$solicitud->id}/foto")
            ->assertStatus(403);

        // 4. No autenticado -> 401 Unauthorized
        \Illuminate\Support\Facades\Auth::logout();
        $this->getJson("/comprobantes/{$solicitud->id}/foto")
            ->assertStatus(401);

        // 5. Acceso directo por /storage/comprobantes/... debe ser bloqueado con 403
        $this->get('/storage/comprobantes/recibo_test.jpg')
            ->assertStatus(403);
    }

    /**
     * "Olvidé mi contraseña" cierra y revoca todas las sesiones anteriores.
     */
    public function test_olvide_contrasena_revoca_todas_las_sesiones_anteriores(): void
    {
        $usuario = $this->crearUsuario();

        // Crear tokens de sesión activos
        $usuario->createToken('dispositivo_movil_1');
        $usuario->createToken('dispositivo_movil_2');
        $this->assertCount(2, $usuario->tokens);

        // Crear código de recuperación
        CodigoAcceso::create([
            'correo' => $usuario->correo,
            'codigo_hash' => Hash::make('112233'),
            'proposito' => 'recuperacion',
            'expira_en' => now()->addMinutes(10),
            'intentos' => 0,
        ]);

        // Restablecer contraseña
        $this->postJson('/api/v1/auth/recuperar', [
            'correo' => $usuario->correo,
            'codigo' => '112233',
            'contrasena' => 'NuevaClaveSegura2026!#',
        ])->assertStatus(204);

        // Todos los tokens deben haber sido eliminados
        $this->assertCount(0, $usuario->fresh()->tokens);
    }
}
