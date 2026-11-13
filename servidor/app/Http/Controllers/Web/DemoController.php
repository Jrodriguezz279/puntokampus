<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\PuntoEntrega;
use App\Models\Tienda;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DemoController extends Controller
{
    public function index(): View
    {
        $vendedor = Usuario::where('correo', 'carlos.vendedor@est.utb.edu.ec')->first();
        $tienda = $vendedor ? Tienda::where('usuario_id', $vendedor->id)->first() : null;
        $productos = $tienda ? Producto::where('tienda_id', $tienda->id)->whereNull('eliminado_en')->get() : collect();
        $pedidos = $tienda ? Pedido::where('tienda_id', $tienda->id)->with(['comprador', 'puntoEntrega', 'items'])->orderBy('id', 'desc')->get() : collect();
        $categorias = Categoria::all();
        $puntos = PuntoEntrega::all();

        return view('demo.vendedor', compact('vendedor', 'tienda', 'productos', 'pedidos', 'categorias', 'puntos'));
    }

    public function agregarProducto(Request $request): RedirectResponse
    {
        $request->validate([
            'nombre'      => 'required|string|max:80',
            'descripcion' => 'nullable|string|max:300',
            'precio'      => 'required|numeric|min:0.25|max:50',
        ]);

        $vendedor = Usuario::where('correo', 'carlos.vendedor@est.utb.edu.ec')->first();
        $tienda = Tienda::where('usuario_id', $vendedor->id)->first();

        Producto::create([
            'tienda_id'   => $tienda->id,
            'nombre'      => $request->input('nombre'),
            'descripcion' => $request->input('descripcion'),
            'precio'      => $request->input('precio'),
            'disponible'  => true,
        ]);

        return back()->with('exito', '¡Producto publicado con éxito!');
    }

    public function entregarPedido(string $codigo): RedirectResponse
    {
        $pedido = Pedido::where('codigo', $codigo)->firstOrFail();
        $pedido->update([
            'estado'             => 'entregado',
            'estado_cambiado_en' => now(),
        ]);

        return back()->with('exito', "¡Pedido {$codigo} marcado como Entregado!");
    }

    public function simularPedido(Request $request): RedirectResponse
    {
        $vendedor = Usuario::where('correo', 'carlos.vendedor@est.utb.edu.ec')->first();
        $tienda = Tienda::where('usuario_id', $vendedor->id)->first();
        $compradora = Usuario::where('correo', 'maria.compradora@est.utb.edu.ec')->first();
        $punto = PuntoEntrega::first();
        $primerProducto = Producto::where('tienda_id', $tienda->id)->first();

        $codigo = 'PK-' . strtoupper(substr(uniqid(), -6));

        $pedido = Pedido::create([
            'codigo'           => $codigo,
            'tienda_id'        => $tienda->id,
            'comprador_id'     => $compradora->id,
            'punto_entrega_id' => $punto->id,
            'hora_entrega'     => now()->addHours(2),
            'forma_pago'       => 'efectivo',
            'nota'             => 'Pedido de prueba generado desde la web demo',
            'total'            => $primerProducto ? $primerProducto->precio : 1.50,
            'estado'           => 'pendiente',
        ]);

        return back()->with('exito', "¡Nuevo pedido {$codigo} recibido como vendedor!");
    }
}
