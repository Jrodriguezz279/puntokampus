<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Producto;
use App\Models\PuntoEntrega;
use App\Models\Tienda;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Estudiante Vendedor
        $vendedor = Usuario::updateOrCreate(
            ['correo' => 'carlos.vendedor@est.utb.edu.ec'],
            [
                'nombres'              => 'Carlos Andrés Mendoza',
                'codigo_estudiante'    => '1204567890',
                'facultad'             => 'Facultad de Ciencias de la Salud',
                'carrera'              => 'Enfermería',
                'semestre'             => 4,
                'whatsapp'             => '593987654321',
                'mostrar_whatsapp'     => true,
                'es_admin'             => false,
                'password'             => Hash::make('Vendedor2026!'),
                'estado'               => 'activa',
                'acepto_privacidad_en' => now(),
            ]
        );

        // Token permanente para pruebas API de Carlos
        $vendedor->tokens()->where('name', 'token_demo_carlos')->delete();
        $tokenVendedor = $vendedor->createToken('token_demo_carlos')->plainTextToken;

        // 2. Tienda de Carlos
        $catComida  = Categoria::where('nombre', 'Comida')->first() ?? Categoria::first();
        $catPostres = Categoria::where('nombre', 'Postres y dulces')->first() ?? Categoria::find(2);
        $punto      = PuntoEntrega::where('nombre', 'Biblioteca Central')->first() ?? PuntoEntrega::first();

        $tienda = Tienda::updateOrCreate(
            ['usuario_id' => $vendedor->id],
            [
                'nombre'           => 'Dulces & Snacks UTB',
                'slug'             => 'dulces-y-snacks-utb',
                'descripcion'      => 'Los mejores brownies, empanadas y jugos naturales para recargar energías entre clases.',
                'categoria_1_id'   => $catComida->id,
                'categoria_2_id'   => $catPostres?->id,
                'punto_entrega_id' => $punto->id,
                'horario'          => 'Lunes a Viernes de 09:00 a 14:00',
                'plan'             => 'gratis',
                'estado'           => 'activa',
            ]
        );

        // 3. Productos de la Tienda
        $prod1 = Producto::updateOrCreate(
            ['tienda_id' => $tienda->id, 'nombre' => 'Brownie Artesanal de Chocolate'],
            [
                'descripcion' => 'Delicioso brownie con nueces y chocolate amargo.',
                'precio'      => 1.25,
                'disponible'  => true,
            ]
        );

        $prod2 = Producto::updateOrCreate(
            ['tienda_id' => $tienda->id, 'nombre' => 'Empanada de Pollo al Horno'],
            [
                'descripcion' => 'Empanada crocante rellena de pechuga de pollo y vegetales frescos.',
                'precio'      => 1.00,
                'disponible'  => true,
            ]
        );

        $prod3 = Producto::updateOrCreate(
            ['tienda_id' => $tienda->id, 'nombre' => 'Jugo Natural de Naranja (500ml)'],
            [
                'descripcion' => 'Jugo 100% natural recién exprimido, servido bien helado.',
                'precio'      => 0.75,
                'disponible'  => true,
            ]
        );

        // 4. Estudiante Compradora
        $compradora = Usuario::updateOrCreate(
            ['correo' => 'maria.compradora@est.utb.edu.ec'],
            [
                'nombres'              => 'María José Zambrano',
                'codigo_estudiante'    => '1209876543',
                'facultad'             => 'Facultad de Ciencias Jurídicas',
                'carrera'              => 'Derecho',
                'semestre'             => 2,
                'whatsapp'             => '593991122334',
                'mostrar_whatsapp'     => true,
                'es_admin'             => false,
                'password'             => Hash::make('Compradora2026!'),
                'estado'               => 'activa',
                'acepto_privacidad_en' => now(),
            ]
        );

        $compradora->tokens()->where('name', 'token_demo_maria')->delete();
        $tokenCompradora = $compradora->createToken('token_demo_maria')->plainTextToken;

        // 5. Pedido de Ejemplo de María a la tienda de Carlos
        $pedido = Pedido::updateOrCreate(
            ['codigo' => 'PK-DEMO01'],
            [
                'tienda_id'        => $tienda->id,
                'comprador_id'     => $compradora->id,
                'punto_entrega_id' => $punto->id,
                'hora_entrega'     => now()->addHours(2),
                'forma_pago'       => 'efectivo',
                'nota'             => 'Por favor llevar cambio de $5',
                'total'            => 2.25,
                'estado'           => 'pendiente',
            ]
        );

        // Items del pedido
        PedidoItem::updateOrCreate(
            ['pedido_id' => $pedido->id, 'producto_id' => $prod1->id],
            [
                'nombre_producto' => $prod1->nombre,
                'precio_unitario' => $prod1->precio,
                'cantidad'        => 1,
                'subtotal'        => 1.25,
            ]
        );

        PedidoItem::updateOrCreate(
            ['pedido_id' => $pedido->id, 'producto_id' => $prod2->id],
            [
                'nombre_producto' => $prod2->nombre,
                'precio_unitario' => $prod2->precio,
                'cantidad'        => 1,
                'subtotal'        => 1.00,
            ]
        );

        $this->command->info('Datos demo creados exitosamente:');
        $this->command->info("Token Vendedor Carlos: {$tokenVendedor}");
        $this->command->info("Token Compradora María: {$tokenCompradora}");
    }
}
