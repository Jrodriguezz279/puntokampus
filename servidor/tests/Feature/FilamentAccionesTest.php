<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Reporte;
use App\Models\SolicitudPremium;
use App\Models\Tienda;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class FilamentAccionesTest extends TestCase
{
    use DatabaseTransactions;

    protected function crearAdmin(): Usuario
    {
        return Usuario::create([
            'correo'               => 'admin_' . uniqid() . '@utb.edu.ec',
            'nombres'              => 'Admin Filament',
            'estado'               => 'activa',
            'es_admin'             => true,
            'acepto_privacidad_en' => now(),
            'whatsapp'             => '593990000001',
        ]);
    }

    protected function crearUsuarioYTienda(): array
    {
        $cat = Categoria::first();
        $usuario = Usuario::create([
            'correo'               => 'vendedor_' . uniqid() . '@utb.edu.ec',
            'nombres'              => 'Vendedor Filament',
            'estado'               => 'activa',
            'es_admin'             => false,
            'acepto_privacidad_en' => now(),
            'whatsapp'             => '593990000002',
        ]);

        $tienda = Tienda::create([
            'usuario_id'         => $usuario->id,
            'nombre'             => 'Tienda ' . uniqid(),
            'slug'               => 'tienda-' . uniqid(),
            'categoria_1_id'     => $cat->id,
            'plan'               => 'gratis',
            'estado'             => 'activa',
            'recibiendo_pedidos' => true,
        ]);

        return [$usuario, $tienda];
    }

    public function test_admin_puede_suspender_y_activar_usuario(): void
    {
        $admin = $this->crearAdmin();
        [$usuario, $tienda] = $this->crearUsuarioYTienda();
        $token = $usuario->createToken('test_token')->plainTextToken;

        $this->assertEquals('activa', $usuario->estado);
        $this->assertCount(1, $usuario->tokens);

        // Suspender usuario
        $usuario->update(['estado' => 'desactivada']);
        $usuario->tokens()->delete();

        $usuario->refresh();
        $this->assertEquals('desactivada', $usuario->estado);
        $this->assertCount(0, $usuario->tokens);

        // Reactivar usuario
        $usuario->update(['estado' => 'activa']);
        $usuario->refresh();
        $this->assertEquals('activa', $usuario->estado);
    }

    public function test_admin_puede_dar_de_baja_tienda_y_cancela_pedidos_pendientes(): void
    {
        $admin = $this->crearAdmin();
        [$vendedor, $tienda] = $this->crearUsuarioYTienda();
        [$comprador, $tokenComprador] = [
            Usuario::create([
                'correo'               => 'comprador_' . uniqid() . '@utb.edu.ec',
                'nombres'              => 'Comprador Test',
                'estado'               => 'activa',
                'acepto_privacidad_en' => now(),
                'whatsapp'             => '593990000003',
            ]),
            'fake_token',
        ];

        // Crear pedido pendiente
        $pedido = Pedido::create([
            'codigo'           => strtoupper(Str::random(8)),
            'comprador_id'     => $comprador->id,
            'tienda_id'        => $tienda->id,
            'punto_entrega_id' => 1,
            'total'            => 10.00,
            'estado'           => 'pendiente',
            'forma_pago'       => 'efectivo',
            'hora_entrega'     => now()->addHours(2),
        ]);

        // Dar de baja tienda
        $motivo = 'Incumplimiento reiterado de normas del campus';
        $tienda->update([
            'estado'          => 'dada_de_baja',
            'motivo_baja'     => $motivo,
            'dada_de_baja_en' => now(),
        ]);

        Pedido::where('tienda_id', $tienda->id)
            ->where('estado', 'pendiente')
            ->update([
                'estado'             => 'cancelado',
                'cancelado_por'      => 'sistema',
                'estado_cambiado_en' => now(),
            ]);

        $tienda->refresh();
        $pedido->refresh();

        $this->assertEquals('dada_de_baja', $tienda->estado);
        $this->assertEquals($motivo, $tienda->motivo_baja);
        $this->assertEquals('cancelado', $pedido->estado);
        $this->assertEquals('sistema', $pedido->cancelado_por);

        // Reactivar tienda
        $tienda->update([
            'estado'          => 'activa',
            'motivo_baja'     => null,
            'dada_de_baja_en' => null,
        ]);
        $tienda->refresh();
        $this->assertEquals('activa', $tienda->estado);
        $this->assertNull($tienda->motivo_baja);
    }

    public function test_admin_puede_aprobar_y_rechazar_solicitudes_premium(): void
    {
        $admin = $this->crearAdmin();
        [$vendedor, $tienda] = $this->crearUsuarioYTienda();

        // 1. Solicitud para aprobar
        $solicitudAprobar = SolicitudPremium::create([
            'tienda_id'          => $tienda->id,
            'monto'              => 5.00,
            'numero_comprobante' => 'COMP-' . uniqid(),
            'foto_comprobante'   => 'comprobantes/test.jpg',
            'estado'             => 'pendiente',
        ]);

        $solicitudAprobar->update([
            'estado'       => 'aprobada',
            'revisado_por' => $admin->id,
            'revisado_en'  => now(),
        ]);

        $tienda->update([
            'plan'          => 'premium',
            'premium_hasta' => Carbon::today()->addDays(30),
        ]);

        $solicitudAprobar->refresh();
        $tienda->refresh();

        $this->assertEquals('aprobada', $solicitudAprobar->estado);
        $this->assertEquals('premium', $tienda->plan);
        $this->assertNotNull($tienda->premium_hasta);

        // 2. Solicitud para rechazar con motivo
        $solicitudRechazar = SolicitudPremium::create([
            'tienda_id'          => $tienda->id,
            'monto'              => 5.00,
            'numero_comprobante' => 'COMP-' . uniqid(),
            'foto_comprobante'   => 'comprobantes/test2.jpg',
            'estado'             => 'pendiente',
        ]);

        $motivoRechazo = 'El comprobante no coincide con la cuenta bancaria de PuntoKampus.';
        $solicitudRechazar->update([
            'estado'         => 'rechazada',
            'motivo_rechazo' => $motivoRechazo,
            'revisado_por'   => $admin->id,
            'revisado_en'    => now(),
        ]);

        $solicitudRechazar->refresh();
        $this->assertEquals('rechazada', $solicitudRechazar->estado);
        $this->assertEquals($motivoRechazo, $solicitudRechazar->motivo_rechazo);
    }

    public function test_admin_puede_revisar_reporte_y_cerrar_sin_accion(): void
    {
        $admin = $this->crearAdmin();
        [$vendedor, $tienda] = $this->crearUsuarioYTienda();

        $reporte = Reporte::create([
            'tienda_id'     => $tienda->id,
            'reportado_por' => $vendedor->id,
            'motivo'        => 'trato_inadecuado',
            'detalle'       => 'Reporte de prueba para revisión de administración.',
            'estado'        => 'nuevo',
        ]);

        $notaAdmin = 'Se contactó a ambas partes y se resolvió amistosamente.';
        $reporte->update([
            'estado'       => 'sin_accion',
            'revisado_por' => $admin->id,
            'revisado_en'  => now(),
            'nota_admin'   => $notaAdmin,
        ]);

        $reporte->refresh();
        $this->assertEquals('sin_accion', $reporte->estado);
        $this->assertEquals($admin->id, $reporte->revisado_por);
        $this->assertEquals($notaAdmin, $reporte->nota_admin);
    }

    public function test_recursos_filament_cargan_modelos_correctamente(): void
    {
        $admin = $this->crearAdmin();
        [$vendedor, $tienda] = $this->crearUsuarioYTienda();

        $producto = Producto::create([
            'tienda_id'        => $tienda->id,
            'nombre'           => 'Torta de Frutilla',
            'precio'           => 2.50,
            'precio_descuento' => 2.00,
            'descuento_activo' => true,
            'disponible'       => true,
        ]);

        $this->assertEquals(Producto::class, \App\Filament\Resources\ProductoResource::getModel());
        $this->assertEquals(Usuario::class, \App\Filament\Resources\UsuarioResource::getModel());
        $this->assertEquals(Tienda::class, \App\Filament\Resources\TiendaResource::getModel());
        $this->assertEquals(SolicitudPremium::class, \App\Filament\Resources\SolicitudPremiumResource::getModel());
        $this->assertEquals(Reporte::class, \App\Filament\Resources\ReporteResource::getModel());
        $this->assertEquals(Pedido::class, \App\Filament\Resources\PedidoResource::getModel());
    }
}
