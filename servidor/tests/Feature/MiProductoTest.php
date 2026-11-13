<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Tienda;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MiProductoTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    protected function crearVendedorConTienda(): array
    {
        $usuario = Usuario::create([
            'correo' => 'vendedor_' . uniqid() . '@utb.edu.ec',
            'nombres' => 'Vendedor Productos',
            'estado' => 'activa',
            'acepto_privacidad_en' => now(),
            'whatsapp' => '593991234567',
        ]);

        $cat = Categoria::first();
        $tienda = Tienda::create([
            'usuario_id' => $usuario->id,
            'nombre' => 'Tienda de Productos ' . uniqid(),
            'slug' => 'tienda-prod-' . uniqid(),
            'categoria_1_id' => $cat->id,
            'plan' => 'gratis',
            'estado' => 'activa',
        ]);

        $token = $usuario->createToken('test')->plainTextToken;

        return [$usuario, $tienda, $token];
    }

    /**
     * H07: Agregar producto con nombre, precio y disponibilidad.
     */
    public function test_crear_producto_exitosamente(): void
    {
        [$usuario, $tienda, $token] = $this->crearVendedorConTienda();

        $res = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/mi-tienda/productos', [
                'nombre' => 'Empanada de Queso',
                'descripcion' => 'Recién horneada con queso manaba',
                'precio' => 1.50,
                'disponible' => true,
            ]);

        $res->assertStatus(201)
            ->assertJson([
                'producto' => [
                    'nombre' => 'Empanada de Queso',
                    'precio' => 1.5,
                    'disponible' => true,
                ],
            ]);

        $this->assertDatabaseHas('productos', [
            'tienda_id' => $tienda->id,
            'nombre' => 'Empanada de Queso',
        ]);
    }

    /**
     * R7: Precio 0 o > 500 devuelve error de validación.
     */
    public function test_precio_invalido_devuelve_error(): void
    {
        [$usuario, $tienda, $token] = $this->crearVendedorConTienda();

        // Precio 0
        $res0 = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/mi-tienda/productos', [
                'nombre' => 'Producto Gratis',
                'precio' => 0,
            ]);
        $res0->assertStatus(422);

        // Precio > 500
        $res500 = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/mi-tienda/productos', [
                'nombre' => 'Producto Caro',
                'precio' => 500.01,
            ]);
        $res500->assertStatus(422);
    }

    /**
     * R7 & H07: Hasta 3 fotos por producto. La 4ta es rechazada con mensaje exacto.
     */
    public function test_maximo_tres_fotos_por_producto(): void
    {
        [$usuario, $tienda, $token] = $this->crearVendedorConTienda();

        $producto = Producto::create([
            'tienda_id' => $tienda->id,
            'nombre' => 'Producto Fotos',
            'precio' => 2.00,
            'disponible' => true,
        ]);

        // Subir 3 fotos exitosamente
        for ($i = 1; $i <= 3; $i++) {
            $res = $this->withHeader('Authorization', "Bearer {$token}")
                ->postJson("/api/v1/mi-tienda/productos/{$producto->id}/fotos", [
                    'foto' => UploadedFile::fake()->image("foto{$i}.jpg", 500, 500),
                ]);
            $res->assertStatus(201);
        }

        $this->assertEquals(3, $producto->fotos()->count());

        // La 4ta foto falla con mensaje exacto
        $res4 = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/mi-tienda/productos/{$producto->id}/fotos", [
                'foto' => UploadedFile::fake()->image('foto4.jpg', 500, 500),
            ]);

        $res4->assertStatus(422);
        $this->assertStringContainsString('Cada producto puede tener hasta 3 fotos.', $res4->getContent());
    }

    /**
     * H07: Borrar producto hace borrado suave (se mantiene en BD).
     */
    public function test_borrar_producto_hace_soft_delete(): void
    {
        [$usuario, $tienda, $token] = $this->crearVendedorConTienda();

        $producto = Producto::create([
            'tienda_id' => $tienda->id,
            'nombre' => 'Producto a borrar',
            'precio' => 3.00,
            'disponible' => true,
        ]);

        $res = $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson("/api/v1/mi-tienda/productos/{$producto->id}");

        $res->assertStatus(204);

        $this->assertNotNull($producto->fresh()->eliminado_en);
    }

    /**
     * Aislamiento: Un vendedor ajeno no puede editar ni borrar productos de otra tienda.
     */
    public function test_aislamiento_no_permite_modificar_productos_ajenos(): void
    {
        [$usuario1, $tienda1, $token1] = $this->crearVendedorConTienda();
        [$usuario2, $tienda2, $token2] = $this->crearVendedorConTienda();

        $productoTienda1 = Producto::create([
            'tienda_id' => $tienda1->id,
            'nombre' => 'Producto Tienda 1',
            'precio' => 2.50,
            'disponible' => true,
        ]);

        // Usuario 2 intenta editar producto de Usuario 1
        $res = $this->withHeader('Authorization', "Bearer {$token2}")
            ->putJson("/api/v1/mi-tienda/productos/{$productoTienda1->id}", [
                'nombre' => 'Hackeado',
                'precio' => 1.00,
            ]);

        $res->assertStatus(404);
        $this->assertEquals('Producto Tienda 1', $productoTienda1->fresh()->nombre);
    }
}
