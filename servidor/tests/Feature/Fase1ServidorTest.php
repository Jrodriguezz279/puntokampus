<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Usuario;
use App\Models\Categoria;
use App\Models\Configuracion;
use App\Models\PuntoEntrega;
use Illuminate\Support\Facades\DB;

class Fase1ServidorTest extends TestCase
{
    public function test_endpoint_salud_responde_200(): void
    {
        $response = $this->getJson('/api/v1/salud');

        $response->assertStatus(200)
            ->assertJson([
                'estado' => 'ok',
                'version' => '1.0',
            ]);
    }

    public function test_extension_unaccent_esta_instalada(): void
    {
        $ext = DB::select("SELECT extname FROM pg_extension WHERE extname = 'unaccent'");
        $this->assertNotEmpty($ext);
        $this->assertEquals('unaccent', $ext[0]->extname);
    }

    public function test_tablas_principales_existen_en_base_de_datos(): void
    {
        $tablasRequeridas = [
            'usuarios',
            'codigos_acceso',
            'categorias',
            'puntos_entrega',
            'tiendas',
            'productos',
            'producto_fotos',
            'pedidos',
            'pedido_items',
            'reportes',
            'solicitudes_premium',
            'configuracion',
        ];

        foreach ($tablasRequeridas as $tabla) {
            $this->assertTrue(
                DB::getSchemaBuilder()->hasTable($tabla),
                "La tabla [{$tabla}] no existe en la base de datos."
            );
        }
    }

    public function test_seeders_cargaron_datos_iniciales(): void
    {
        $this->assertEquals(8, Categoria::count());
        $this->assertGreaterThanOrEqual(1, PuntoEntrega::count());
        $this->assertNotNull(Configuracion::first());
        $this->assertDatabaseHas('usuarios', [
            'correo' => 'admin@utb.edu.ec',
            'es_admin' => true,
        ]);
    }

    public function test_admin_puede_acceder_a_panel_y_usuario_normal_no(): void
    {
        $admin = Usuario::where('correo', 'admin@utb.edu.ec')->first();
        $this->assertTrue($admin->canAccessPanel(new \Filament\Panel()));

        $usuarioNormal = new Usuario([
            'correo' => 'estudiante@utb.edu.ec',
            'nombres' => 'Estudiante Prueba',
            'es_admin' => false,
            'estado' => 'activa',
        ]);
        $this->assertFalse($usuarioNormal->canAccessPanel(new \Filament\Panel()));
    }
}
