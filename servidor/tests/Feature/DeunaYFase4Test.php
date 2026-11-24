<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Producto;
use App\Models\PuntoEntrega;
use App\Models\Tienda;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class DeunaYFase4Test extends TestCase
{
    use DatabaseTransactions;

    protected function crearVendedor(): array
    {
        $cat = Categoria::first();
        $vendedor = Usuario::create([
            'correo'               => 'vendedor_' . uniqid() . '@utb.edu.ec',
            'nombres'              => 'Vendedor Test',
            'estado'               => 'activa',
            'acepto_privacidad_en' => now(),
            'whatsapp'             => '593991000001',
        ]);
        $tienda = Tienda::create([
            'usuario_id'         => $vendedor->id,
            'nombre'             => 'Tienda ' . uniqid(),
            'slug'               => 'tienda-' . uniqid(),
            'categoria_1_id'     => $cat->id,
            'plan'               => 'gratis',
            'estado'             => 'activa',
            'recibiendo_pedidos' => true,
        ]);
        $token = $vendedor->createToken('test')->plainTextToken;
        return [$vendedor, $tienda, $token];
    }

    protected function crearComprador(): array
    {
        $comprador = Usuario::create([
            'correo'               => 'comprador_' . uniqid() . '@utb.edu.ec',
            'nombres'              => 'Comprador Test',
            'estado'               => 'activa',
            'acepto_privacidad_en' => now(),
            'whatsapp'             => '593992000002',
        ]);
        $token = $comprador->createToken('test')->plainTextToken;
        return [$comprador, $token];
    }

    public function test_vendedor_puede_subir_y_eliminar_qr_deuna(): void
    {
        Storage::fake('public');
        [$vendedor, $tienda, $token] = $this->crearVendedor();

        $archivo = UploadedFile::fake()->image('deuna_qr.png', 400, 400);

        // Subir QR de Deuna
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/mi-tienda/deuna-qr', [
                'foto' => $archivo,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['mensaje', 'deuna_qr_url', 'tienda']);

        $tienda->refresh();
        $this->assertNotNull($tienda->deuna_qr_url);

        // Comprobar que en /mi-tienda el dueño sí ve su deuna_qr_url
        $resMiTienda = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/mi-tienda');
        $resMiTienda->assertStatus(200);
        $this->assertNotNull($resMiTienda->json('tienda.deuna_qr_url'));

        // Eliminar QR de Deuna
        $resDelete = $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson('/api/v1/mi-tienda/deuna-qr');

        $resDelete->assertStatus(204);
        $tienda->refresh();
        $this->assertNull($tienda->deuna_qr_url);
    }

    public function test_deuna_qr_es_privado_y_no_se_muestra_en_vitrina_publica(): void
    {
        Storage::fake('public');
        [$vendedor, $tienda, $token] = $this->crearVendedor();
        $tienda->update(['deuna_qr_url' => 'https://ejemplo.com/storage/deuna_qrs/test.png']);

        // 1. Visitante público en listado de tiendas
        $resListado = $this->getJson('/api/v1/tiendas');
        $resListado->assertStatus(200);
        $tiendas = $resListado->json('tiendas');
        $tiendaEncontrada = collect($tiendas)->firstWhere('id', $tienda->id);
        $this->assertNotNull($tiendaEncontrada);
        $this->assertArrayNotHasKey('deuna_qr_url', $tiendaEncontrada);

        // 2. Visitante público en ver tienda por slug
        $resSlug = $this->getJson("/api/v1/tiendas/{$tienda->slug}");
        $resSlug->assertStatus(200);
        $this->assertArrayNotHasKey('deuna_qr_url', $resSlug->json('tienda'));
    }

    public function test_comprador_ve_deuna_qr_solo_si_eligio_pago_deuna(): void
    {
        [$vendedor, $tienda, $tokenVendedor] = $this->crearVendedor();
        [$comprador, $tokenComprador] = $this->crearComprador();
        $punto = PuntoEntrega::first();

        $tienda->update(['deuna_qr_url' => 'https://ejemplo.com/storage/deuna_qrs/deuna_test.png']);

        // Pedido con pago deuna
        $pedidoDeuna = Pedido::create([
            'codigo'             => strtoupper(Str::random(8)),
            'comprador_id'       => $comprador->id,
            'tienda_id'          => $tienda->id,
            'punto_entrega_id'   => $punto->id,
            'total'              => 5.00,
            'estado'             => 'pendiente',
            'forma_pago'         => 'deuna',
            'hora_entrega'       => now()->addHours(2),
            'codigo_entrega'     => Crypt::encryptString('1234'),
        ]);

        // Pedido con pago efectivo
        $pedidoEfectivo = Pedido::create([
            'codigo'             => strtoupper(Str::random(8)),
            'comprador_id'       => $comprador->id,
            'tienda_id'          => $tienda->id,
            'punto_entrega_id'   => $punto->id,
            'total'              => 5.00,
            'estado'             => 'pendiente',
            'forma_pago'         => 'efectivo',
            'hora_entrega'       => now()->addHours(2),
            'codigo_entrega'     => Crypt::encryptString('5678'),
        ]);

        // Comprador consulta pedido con deuna
        $resDeuna = $this->withHeader('Authorization', "Bearer {$tokenComprador}")
            ->getJson("/api/v1/mis-pedidos/{$pedidoDeuna->codigo}");
        $resDeuna->assertStatus(200);
        $this->assertEquals('https://ejemplo.com/storage/deuna_qrs/deuna_test.png', $resDeuna->json('data.deuna_qr_url'));

        // Comprador consulta pedido con efectivo -> deuna_qr_url es null
        $resEfectivo = $this->withHeader('Authorization', "Bearer {$tokenComprador}")
            ->getJson("/api/v1/mis-pedidos/{$pedidoEfectivo->codigo}");
        $resEfectivo->assertStatus(200);
        $this->assertNull($resEfectivo->json('data.deuna_qr_url'));
    }

    public function test_descuento_precio_menor_estricto_y_mensaje_de_validacion(): void
    {
        [$vendedor, $tienda, $token] = $this->crearVendedor();

        // Intento de crear producto con precio_descuento >= precio
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/mi-tienda/productos', [
                'nombre'           => 'Hamburguesa Especial',
                'precio'           => 3.50,
                'precio_descuento' => 3.50, // igual que el precio
                'descuento_activo' => true,
                'disponible'       => true,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['precio_descuento']);

        $errores = $response->json('errors.precio_descuento');
        $this->assertContains('El precio rebajado tiene que ser menor que el precio normal.', $errores);

        // Intento con precio_descuento mayor
        $responseMayor = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/mi-tienda/productos', [
                'nombre'           => 'Hamburguesa Especial 2',
                'precio'           => 3.50,
                'precio_descuento' => 4.00,
                'descuento_activo' => true,
                'disponible'       => true,
            ]);

        $responseMayor->assertStatus(422)
            ->assertJsonValidationErrors(['precio_descuento']);
    }

    public function test_pedido_calcula_total_con_precio_descuento_cuando_esta_activo(): void
    {
        [$vendedor, $tienda, $tokenVendedor] = $this->crearVendedor();
        [$comprador, $tokenComprador] = $this->crearComprador();
        $punto = PuntoEntrega::first();

        $producto = Producto::create([
            'tienda_id'        => $tienda->id,
            'nombre'           => 'Pastel de Chocolate',
            'precio'           => 5.00,
            'precio_descuento' => 3.50,
            'descuento_activo' => true,
            'disponible'       => true,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$tokenComprador}")
            ->postJson('/api/v1/pedidos', [
                'tienda_id'        => $tienda->id,
                'punto_entrega_id' => $punto->id,
                'forma_pago'       => 'deuna',
                'hora_entrega'     => now()->addHours(2)->format('Y-m-d H:i:s'),
                'items'            => [
                    [
                        'producto_id' => $producto->id,
                        'cantidad'    => 2,
                    ],
                ],
            ]);

        $response->assertStatus(201);
        // 2 unidades a $3.50 = $7.00
        $this->assertEquals(7.00, (float) $response->json('data.total'));

        $pedido = Pedido::where('codigo', $response->json('data.codigo'))->first();
        $this->assertNotNull($pedido);
        $this->assertEquals(7.00, (float) $pedido->total);

        $item = $pedido->items->first();
        $this->assertEquals(3.50, (float) $item->precio_unitario);
    }

    public function test_entrega_guarda_entregado_con_codigo_y_boton(): void
    {
        [$vendedor, $tienda, $tokenVendedor] = $this->crearVendedor();
        [$comprador, $tokenComprador] = $this->crearComprador();
        $punto = PuntoEntrega::first();

        // 1. Pedido para entrega con código
        $pedidoCodigo = Pedido::create([
            'codigo'             => strtoupper(Str::random(8)),
            'comprador_id'       => $comprador->id,
            'tienda_id'          => $tienda->id,
            'punto_entrega_id'   => $punto->id,
            'total'              => 10.00,
            'estado'             => 'pendiente',
            'forma_pago'         => 'efectivo',
            'hora_entrega'       => now()->addHours(2),
            'codigo_entrega'     => Crypt::encryptString('4567'),
        ]);

        $resCodigo = $this->withHeader('Authorization', "Bearer {$tokenVendedor}")
            ->postJson("/api/v1/mi-tienda/pedidos/{$pedidoCodigo->codigo}/confirmar-entrega", [
                'codigo_entrega' => '4567',
            ]);

        $resCodigo->assertStatus(200);
        $pedidoCodigo->refresh();
        $this->assertEquals('entregado', $pedidoCodigo->estado);
        $this->assertEquals('codigo', $pedidoCodigo->entregado_con);

        // 2. Pedido para entrega con botón
        $pedidoBoton = Pedido::create([
            'codigo'             => strtoupper(Str::random(8)),
            'comprador_id'       => $comprador->id,
            'tienda_id'          => $tienda->id,
            'punto_entrega_id'   => $punto->id,
            'total'              => 10.00,
            'estado'             => 'pendiente',
            'forma_pago'         => 'efectivo',
            'hora_entrega'       => now()->addHours(2),
            'codigo_entrega'     => Crypt::encryptString('8901'),
        ]);

        $resBoton = $this->withHeader('Authorization', "Bearer {$tokenVendedor}")
            ->postJson("/api/v1/mi-tienda/pedidos/{$pedidoBoton->codigo}/entregado");

        $resBoton->assertStatus(200);
        $pedidoBoton->refresh();
        $this->assertEquals('entregado', $pedidoBoton->estado);
        $this->assertEquals('boton', $pedidoBoton->entregado_con);
    }

    public function test_porcentaje_descuento_calcula_correctamente(): void
    {
        [$vendedor, $tienda, $token] = $this->crearVendedor();

        $producto = Producto::create([
            'tienda_id'        => $tienda->id,
            'nombre'           => 'Postre Tres Leches',
            'precio'           => 10.00,
            'precio_descuento' => 7.00,
            'descuento_activo' => true,
            'disponible'       => true,
        ]);

        $this->assertEquals(30, $producto->descuento_porcentaje);
        $this->assertEquals(30, $producto->porcentaje_descuento);

        $resource = (new \App\Http\Resources\ProductoResource($producto))->toArray(request());
        $this->assertEquals(30, $resource['descuento_porcentaje']);
        $this->assertEquals(30, $resource['porcentaje_descuento']);
        $this->assertTrue($resource['descuento_activo']);
        $this->assertTrue($resource['tiene_descuento']);
    }
}
