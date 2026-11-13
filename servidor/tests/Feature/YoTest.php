<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class YoTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * H03: Salir revoca el token y el token deja de servir.
     */
    public function test_salir_revoca_el_token(): void
    {
        $usuario = Usuario::create([
            'correo' => 'usuario_salir_' . uniqid() . '@utb.edu.ec',
            'nombres' => 'Usuario Salir',
            'estado' => 'activa',
            'acepto_privacidad_en' => now(),
        ]);

        $token = $usuario->createToken('test_token')->plainTextToken;

        // Comprueba acceso inicial
        $resAuth = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/yo');
        $resAuth->assertStatus(200);

        // Hace logout
        $resSalir = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/auth/salir');
        $resSalir->assertStatus(204);

        // Resetear la autenticación en el test para forzar re-evaluación del token en el siguiente request
        $this->app['auth']->forgetGuards();

        // Ya no puede ingresar con el mismo token
        $resRevocado = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/yo');
        $resRevocado->assertStatus(401);
    }

    /**
     * R3 & H01: Completar nombres y aceptar política de privacidad.
     */
    public function test_actualizar_perfil_exige_politica_en_nuevo_usuario(): void
    {
        $usuario = Usuario::create([
            'correo' => 'perfil_' . uniqid() . '@utb.edu.ec',
            'nombres' => '',
            'estado' => 'activa',
            'acepto_privacidad_en' => null,
        ]);

        $token = $usuario->createToken('test_token')->plainTextToken;

        // Falla si no acepta política
        $res1 = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/v1/yo', [
                'nombres' => 'Juan Pérez',
                'acepto_privacidad' => false,
            ]);
        $res1->assertStatus(422)
            ->assertJsonValidationErrors(['acepto_privacidad']);

        // Exitoso al aceptar política
        $res2 = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/v1/yo', [
                'nombres' => 'Juan Pérez',
                'acepto_privacidad' => true,
            ]);
        $res2->assertStatus(200)
            ->assertJson([
                'usuario' => [
                    'nombres' => 'Juan Pérez',
                ],
            ]);

        $this->assertNotNull($usuario->fresh()->acepto_privacidad_en);
    }

    /**
     * Aislamiento: Ruta protegida sin token retorna 401.
     */
    public function test_yo_sin_token_retorna_401(): void
    {
        $response = $this->getJson('/api/v1/yo');
        $response->assertStatus(401);
    }
}
