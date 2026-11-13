<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PaginasPublicasTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function test_privacidad_devuelve_200(): void
    {
        $response = $this->get('/privacidad');

        $response->assertStatus(200);
        $response->assertSee('PuntoKampus');
        $response->assertSee('Política de Privacidad');
    }

    /** @test */
    public function test_borrar_cuenta_web_devuelve_200(): void
    {
        $response = $this->get('/borrar-cuenta');

        $response->assertStatus(200);
        $response->assertSee('Eliminar mi cuenta');
    }

    /** @test */
    public function test_borrar_cuenta_web_rechaza_correo_no_utb(): void
    {
        $response = $this->post('/borrar-cuenta/codigo', [
            '_token' => csrf_token(),
            'correo' => 'alguien@gmail.com',
        ]);

        $response->assertStatus(302); // redirect back
        $response->assertSessionHasErrors(['correo']);
    }

    /** @test */
    public function test_borrar_cuenta_web_correo_no_registrado_da_error(): void
    {
        $response = $this->post('/borrar-cuenta/codigo', [
            '_token' => csrf_token(),
            'correo' => 'noexiste_' . uniqid() . '@est.utb.edu.ec',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['correo']);
    }
}

