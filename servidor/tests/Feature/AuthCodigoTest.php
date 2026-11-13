<?php

namespace Tests\Feature;

use App\Mail\CodigoAccesoMailable;
use App\Models\CodigoAcceso;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AuthCodigoTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    /**
     * R1: Solo correos que terminan en utb.edu.ec y subdominios son aceptados.
     */
    public function test_rechaza_correo_que_no_sea_utb(): void
    {
        $response = $this->postJson('/api/v1/auth/codigo', [
            'correo' => 'juanperez@gmail.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['correo'])
            ->assertJsonFragment([
                'correo' => ['Solo se puede entrar con un correo de la UTB (termina en utb.edu.ec).'],
            ]);

        Mail::assertNothingSent();
    }

    public function test_acepta_correo_institucional_directo_y_subdominio(): void
    {
        $res1 = $this->postJson('/api/v1/auth/codigo', [
            'correo' => 'estudiante1_' . uniqid() . '@utb.edu.ec',
        ]);
        $res1->assertStatus(204);

        $res2 = $this->postJson('/api/v1/auth/codigo', [
            'correo' => 'estudiante2_' . uniqid() . '@fafi.utb.edu.ec',
        ]);
        $res2->assertStatus(204);

        Mail::assertSent(CodigoAccesoMailable::class, 2);
    }

    /**
     * R2: Para pedir código nuevo hay que esperar 60 s.
     */
    public function test_throttling_sesenta_segundos_entre_solicitudes(): void
    {
        $correo = 'throttling_' . uniqid() . '@utb.edu.ec';

        $res1 = $this->postJson('/api/v1/auth/codigo', ['correo' => $correo]);
        $res1->assertStatus(204);

        $res2 = $this->postJson('/api/v1/auth/codigo', ['correo' => $correo]);
        $res2->assertStatus(429);
    }

    /**
     * R2: Máximo 5 códigos por correo cada hora.
     */
    public function test_throttling_maximo_cinco_codigos_por_hora(): void
    {
        $correo = 'maxcodigos_' . uniqid() . '@utb.edu.ec';

        for ($i = 0; $i < 5; $i++) {
            CodigoAcceso::create([
                'correo' => $correo,
                'codigo_hash' => Hash::make('123456'),
                'expira_en' => now()->addMinutes(10),
                'creado_en' => now()->subMinutes(10 + $i),
                'actualizado_en' => now()->subMinutes(10 + $i),
            ]);
        }

        $res = $this->postJson('/api/v1/auth/codigo', ['correo' => $correo]);
        $res->assertStatus(429);
    }

    /**
     * R5: Cuenta desactivada no puede solicitar código.
     */
    public function test_cuenta_desactivada_no_puede_solicitar_codigo(): void
    {
        $correo = 'desactivado_' . uniqid() . '@utb.edu.ec';
        Usuario::create([
            'correo' => $correo,
            'nombres' => 'Usuario Desactivado',
            'estado' => 'desactivada',
        ]);

        $res = $this->postJson('/api/v1/auth/codigo', [
            'correo' => $correo,
        ]);

        $res->assertStatus(403);
    }
}
