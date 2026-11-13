<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\PuntoEntrega;
use App\Models\Tienda;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MiTiendaTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    protected function crearUsuario(array $atributos = []): Usuario
    {
        return Usuario::create(array_merge([
            'correo' => 'vendedor_' . uniqid() . '@utb.edu.ec',
            'nombres' => 'Vendedor Prueba',
            'estado' => 'activa',
            'acepto_privacidad_en' => now(),
        ], $atributos));
    }

    /**
     * R6 & H05: Crear tienda exitosamente con formateo de WhatsApp y generación de slug.
     */
    public function test_crear_tienda_exitosamente(): void
    {
        $usuario = $this->crearUsuario();
        $token = $usuario->createToken('test')->plainTextToken;

        $cat1 = Categoria::first();
        $cat2 = Categoria::skip(1)->first();
        $punto = PuntoEntrega::first();

        $res = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/mi-tienda', [
                'nombre' => 'Dulces de Ana',
                'descripcion' => 'Postres caseros en la biblioteca',
                'categoria_1_id' => $cat1->id,
                'categoria_2_id' => $cat2->id,
                'horario' => 'Lunes a viernes, 8:00 a 14:00',
                'punto_entrega_id' => $punto->id,
                'codigo_estudiante' => 'UTB-12345',
                'facultad' => 'FAFI',
                'carrera' => 'Sistemas',
                'semestre' => 6,
                'whatsapp' => '0991234567',
            ]);

        $res->assertStatus(201)
            ->assertJson([
                'mensaje' => '¡Tu tienda ya está en la vitrina!',
                'tienda' => [
                    'nombre' => 'Dulces de Ana',
                    'slug' => 'dulces-de-ana',
                    'plan' => 'gratis',
                    'insignia' => 'Matriculado',
                ],
            ]);

        // Verifica que se guardó el WhatsApp formateado a internacional 5939XXXXXXXX
        $this->assertEquals('593991234567', $usuario->fresh()->whatsapp);
    }

    /**
     * R6: No permite crear una segunda tienda a la misma cuenta.
     */
    public function test_no_permite_crear_mas_de_una_tienda_por_usuario(): void
    {
        $usuario = $this->crearUsuario();
        $token = $usuario->createToken('test')->plainTextToken;
        $cat = Categoria::first();

        Tienda::create([
            'usuario_id' => $usuario->id,
            'nombre' => 'Primera Tienda',
            'slug' => 'primera-tienda',
            'categoria_1_id' => $cat->id,
            'plan' => 'gratis',
            'estado' => 'activa',
        ]);

        $res = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/mi-tienda', [
                'nombre' => 'Segunda Tienda',
                'categoria_1_id' => $cat->id,
            ]);

        $res->assertStatus(422);
    }

    /**
     * R6: Misma categoría dos veces devuelve error de validación.
     */
    public function test_misma_categoria_dos_veces_es_rechazada(): void
    {
        $usuario = $this->crearUsuario();
        $token = $usuario->createToken('test')->plainTextToken;
        $cat = Categoria::first();

        $res = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/mi-tienda', [
                'nombre' => 'Tienda Categorias Repetidas',
                'categoria_1_id' => $cat->id,
                'categoria_2_id' => $cat->id,
                'codigo_estudiante' => 'UTB-12345',
                'facultad' => 'FAFI',
                'carrera' => 'Sistemas',
                'semestre' => 6,
                'whatsapp' => '0991234567',
            ]);

        $res->assertStatus(422)
            ->assertJsonValidationErrors(['categoria_2_id']);
    }

    /**
     * R10: Portada rechazada (403) si la tienda no es Premium.
     */
    public function test_subir_portada_falla_en_plan_gratis(): void
    {
        $usuario = $this->crearUsuario();
        $token = $usuario->createToken('test')->plainTextToken;
        $cat = Categoria::first();

        Tienda::create([
            'usuario_id' => $usuario->id,
            'nombre' => 'Tienda Gratis',
            'slug' => 'tienda-gratis',
            'categoria_1_id' => $cat->id,
            'plan' => 'gratis',
            'estado' => 'activa',
        ]);

        $foto = UploadedFile::fake()->image('portada.jpg', 1200, 600);

        $res = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/mi-tienda/portada', [
                'foto' => $foto,
            ]);

        $res->assertStatus(403);
    }

    /**
     * R10: Portada permitida en tienda Premium.
     */
    public function test_subir_portada_exitosa_en_plan_premium(): void
    {
        $usuario = $this->crearUsuario();
        $token = $usuario->createToken('test')->plainTextToken;
        $cat = Categoria::first();

        $tienda = Tienda::create([
            'usuario_id' => $usuario->id,
            'nombre' => 'Tienda Premium',
            'slug' => 'tienda-premium',
            'categoria_1_id' => $cat->id,
            'plan' => 'premium',
            'estado' => 'activa',
        ]);

        $foto = UploadedFile::fake()->image('portada.jpg', 800, 400);

        $res = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/mi-tienda/portada', [
                'foto' => $foto,
            ]);

        $res->assertStatus(200)
            ->assertJsonStructure(['portada_url']);

        $this->assertNotNull($tienda->fresh()->portada_url);
    }

    /**
     * H06: Subir logo permitido en cualquier plan.
     */
    public function test_subir_logo_permitido_en_plan_gratis(): void
    {
        $usuario = $this->crearUsuario();
        $token = $usuario->createToken('test')->plainTextToken;
        $cat = Categoria::first();

        $tienda = Tienda::create([
            'usuario_id' => $usuario->id,
            'nombre' => 'Tienda con Logo',
            'slug' => 'tienda-logo',
            'categoria_1_id' => $cat->id,
            'plan' => 'gratis',
            'estado' => 'activa',
        ]);

        $foto = UploadedFile::fake()->image('logo.jpg', 400, 400);

        $res = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/mi-tienda/logo', [
                'foto' => $foto,
            ]);

        $res->assertStatus(200)
            ->assertJsonStructure(['logo_url']);

        $this->assertNotNull($tienda->fresh()->logo_url);
    }
}

