<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Tienda;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class VitrinaTest extends TestCase
{
    use DatabaseTransactions;

    protected function crearTiendaConProducto(string $nombreTienda, string $nombreProducto, ?string $estadoTienda = 'activa'): array
    {
        $usuario = Usuario::create([
            'correo' => 'vendedor_' . uniqid() . '@utb.edu.ec',
            'nombres' => 'Vendedor Vitrina',
            'estado' => 'activa',
            'acepto_privacidad_en' => now(),
        ]);

        $cat = Categoria::first();
        $tienda = Tienda::create([
            'usuario_id' => $usuario->id,
            'nombre' => $nombreTienda,
            'slug' => \Illuminate\Support\Str::slug($nombreTienda) . '-' . uniqid(),
            'categoria_1_id' => $cat->id,
            'plan' => 'gratis',
            'estado' => $estadoTienda,
        ]);

        $producto = Producto::create([
            'tienda_id' => $tienda->id,
            'nombre' => $nombreProducto,
            'descripcion' => 'Descripción de ' . $nombreProducto,
            'precio' => 2.00,
            'disponible' => true,
        ]);

        return [$tienda, $producto];
    }

    /**
     * H09: Categorías y Puntos de Entrega activos se listan públicamente.
     */
    public function test_categorias_y_puntos_activos_se_listan_publicamente(): void
    {
        $resCat = $this->getJson('/api/v1/categorias');
        $resCat->assertStatus(200)
            ->assertJsonStructure(['categorias' => [['id', 'nombre', 'orden']]]);

        $resPto = $this->getJson('/api/v1/puntos-entrega');
        $resPto->assertStatus(200)
            ->assertJsonStructure(['puntos' => [['id', 'nombre', 'orden']]]);

        $resConf = $this->getJson('/api/v1/configuracion-publica');
        $resConf->assertStatus(200)
            ->assertJsonStructure(['instagram_url', 'tiktok_url', 'facebook_url']);
    }

    /**
     * H10: Búsqueda sin distinguir mayúsculas ni tildes (PostgreSQL unaccent).
     */
    public function test_busqueda_insensible_a_tildes_y_mayusculas(): void
    {
        $this->crearTiendaConProducto('Empanadas Doña María', 'Empanada de carne');

        // 1. Minúsculas sin tilde
        $res1 = $this->getJson('/api/v1/productos?buscar=empanada');
        $res1->assertStatus(200);
        $this->assertNotEmpty($res1->json('productos'));

        // 2. Mayúsculas
        $res2 = $this->getJson('/api/v1/productos?buscar=EMPANADA');
        $res2->assertStatus(200);
        $this->assertNotEmpty($res2->json('productos'));

        // 3. Con tildes invertidas o diferentes ("empanadá" o "maría" vs "maria")
        $res3 = $this->getJson('/api/v1/tiendas?buscar=maria');
        $res3->assertStatus(200);
        $this->assertNotEmpty($res3->json('tiendas'));

        $res4 = $this->getJson('/api/v1/tiendas?buscar=MARÍA');
        $res4->assertStatus(200);
        $this->assertNotEmpty($res4->json('tiendas'));
    }

    /**
     * H11 & H12: Ver tienda pública activa por slug.
     */
    public function test_ver_tienda_publica_por_slug(): void
    {
        [$tienda, $producto] = $this->crearTiendaConProducto('Pastelería Dulzura', 'Pastel de Chocolate');

        $res = $this->getJson("/api/v1/tiendas/{$tienda->slug}");
        $res->assertStatus(200)
            ->assertJson([
                'tienda' => [
                    'id' => $tienda->id,
                    'nombre' => 'Pastelería Dulzura',
                    'slug' => $tienda->slug,
                    'productos' => [
                        [
                            'id' => $producto->id,
                            'nombre' => 'Pastel de Chocolate',
                        ],
                    ],
                ],
            ]);
    }

    /**
     * R8 & H12: Tienda dada de baja no aparece en vitrina ni búsqueda y su enlace devuelve 404.
     */
    public function test_tienda_dada_de_baja_no_aparece_y_da_404(): void
    {
        [$tiendaBaja, $producto] = $this->crearTiendaConProducto('Tienda Infractora', 'Producto Infractor', 'dada_de_baja');

        // 1. Enlace público devuelve 404 con mensaje exacto
        $resSlug = $this->getJson("/api/v1/tiendas/{$tiendaBaja->slug}");
        $resSlug->assertStatus(404)
            ->assertJson([
                'message' => 'Esta tienda no está disponible.',
            ]);

        // 2. No aparece en lista de tiendas
        $resTiendas = $this->getJson('/api/v1/tiendas?buscar=Infractora');
        $resTiendas->assertStatus(200);
        $this->assertEmpty($resTiendas->json('tiendas'));

        // 3. Sus productos no aparecen en la vitrina de productos
        $resProd = $this->getJson('/api/v1/productos?buscar=Infractor');
        $resProd->assertStatus(200);
        $this->assertEmpty($resProd->json('productos'));
    }
}
