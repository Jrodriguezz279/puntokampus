<?php

namespace Tests\Feature;

use App\Models\CodigoAcceso;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthVerificarTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * R3: Verificación exitosa con creación automática de usuario si es nuevo (H01).
     */
    public function test_verificacion_exitosa_crea_usuario_nuevo_y_devuelve_token(): void
    {
        $correo = 'nuevo.estudiante_' . uniqid() . '@utb.edu.ec';
        $codigo = '654321';

        CodigoAcceso::create([
            'correo' => $correo,
            'codigo_hash' => Hash::make($codigo),
            'expira_en' => now()->addMinutes(10),
            'intentos' => 0,
        ]);

        $res = $this->postJson('/api/v1/auth/verificar', [
            'correo' => $correo,
            'codigo' => $codigo,
            'dispositivo' => 'android',
        ]);

        $res->assertStatus(200)
            ->assertJsonStructure([
                'token',
                'usuario' => ['id', 'correo', 'estado'],
                'es_nuevo',
            ])
            ->assertJson([
                'es_nuevo' => true,
                'usuario' => [
                    'correo' => $correo,
                ],
            ]);

        $this->assertDatabaseHas('usuarios', ['correo' => $correo]);
        $this->assertNotNull(CodigoAcceso::where('correo', $correo)->first()->usado_en);
    }

    /**
     * R3: Código incorrecto decrementa los intentos restantes y muestra mensaje exacto.
     */
    public function test_codigo_incorrecto_decrementa_intentos(): void
    {
        $correo = 'fallo_' . uniqid() . '@utb.edu.ec';
        CodigoAcceso::create([
            'correo' => $correo,
            'codigo_hash' => Hash::make('111111'),
            'expira_en' => now()->addMinutes(10),
            'intentos' => 0,
        ]);

        $res = $this->postJson('/api/v1/auth/verificar', [
            'correo' => $correo,
            'codigo' => '222222',
        ]);

        $res->assertStatus(422)
            ->assertJsonValidationErrors(['codigo'])
            ->assertJsonFragment([
                'codigo' => ['Ese código no es correcto. Te quedan 4 intentos.'],
            ]);
    }

    /**
     * R3: Al 5to fallo el código deja de servir.
     */
    public function test_cinco_fallos_bloquean_el_codigo(): void
    {
        $correo = 'bloqueo_' . uniqid() . '@utb.edu.ec';
        CodigoAcceso::create([
            'correo' => $correo,
            'codigo_hash' => Hash::make('111111'),
            'expira_en' => now()->addMinutes(10),
            'intentos' => 4, // Al siguiente será el 5to
        ]);

        $res = $this->postJson('/api/v1/auth/verificar', [
            'correo' => $correo,
            'codigo' => '000000',
        ]);

        $res->assertStatus(422)
            ->assertJsonFragment([
                'codigo' => ['Hiciste muchos intentos. Espera unos minutos y pide un código nuevo.'],
            ]);
    }

    /**
     * R3: Código expirado > 10 min.
     */
    public function test_codigo_expirado_no_es_valido(): void
    {
        $correo = 'expirado_' . uniqid() . '@utb.edu.ec';
        CodigoAcceso::create([
            'correo' => $correo,
            'codigo_hash' => Hash::make('123456'),
            'expira_en' => now()->subMinutes(1),
            'intentos' => 0,
        ]);

        $res = $this->postJson('/api/v1/auth/verificar', [
            'correo' => $correo,
            'codigo' => '123456',
        ]);

        $res->assertStatus(422)
            ->assertJsonFragment([
                'codigo' => ['Este código ya venció. Pide uno nuevo.'],
            ]);
    }

    /**
     * R5: Cuenta desactivada no puede verificar.
     */
    public function test_cuenta_desactivada_no_puede_verificar(): void
    {
        $correo = 'desact.verif_' . uniqid() . '@utb.edu.ec';
        Usuario::create([
            'correo' => $correo,
            'nombres' => 'Desactivado',
            'estado' => 'desactivada',
        ]);

        CodigoAcceso::create([
            'correo' => $correo,
            'codigo_hash' => Hash::make('123456'),
            'expira_en' => now()->addMinutes(10),
            'intentos' => 0,
        ]);

        $res = $this->postJson('/api/v1/auth/verificar', [
            'correo' => $correo,
            'codigo' => '123456',
        ]);

        $res->assertStatus(403);
    }
}
