<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PuntoKampus — Panel del Estudiante Vendedor</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f3f4f6; color: #111827; }
        header { background: #4f46e5; color: white; padding: 1.25rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        header h1 { font-size: 1.35rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem; }
        .user-tag { background: rgba(255,255,255,0.2); padding: 0.35rem 0.8rem; border-radius: 20px; font-size: 0.85rem; }
        .container { max-width: 1100px; margin: 1.5rem auto; padding: 0 1rem 3rem; }
        .alert-success { background: #dcfce7; border: 1px solid #86efac; color: #15803d; padding: 0.85rem 1.2rem; border-radius: 8px; margin-bottom: 1.5rem; font-weight: 600; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem; }
        @media (max-width: 768px) { .grid-2 { grid-template-columns: 1fr; } }
        .card { background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
        .card h2 { font-size: 1.15rem; font-weight: 700; margin-bottom: 1rem; color: #1f2937; display: flex; align-items: center; justify-content: space-between; }
        .info-row { display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #f3f4f6; font-size: 0.92rem; }
        .info-row span:first-child { color: #6b7280; }
        .badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 12px; font-size: 0.75rem; font-weight: 700; }
        .badge-green { background: #dcfce7; color: #15803d; }
        .badge-amber { background: #fef3c7; color: #b45309; }
        .badge-blue { background: #e0e7ff; color: #4338ca; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.35rem; }
        input[type="text"], input[type="number"], textarea { width: 100%; padding: 0.6rem 0.8rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.92rem; }
        input:focus, textarea:focus { border-color: #4f46e5; outline: none; }
        .btn { padding: 0.6rem 1.1rem; border-radius: 6px; font-size: 0.9rem; font-weight: 600; cursor: pointer; border: none; transition: opacity 0.2s; }
        .btn:hover { opacity: 0.9; }
        .btn-primary { background: #4f46e5; color: white; width: 100%; }
        .btn-success { background: #10b981; color: white; }
        .btn-secondary { background: #e5e7eb; color: #374151; }
        .table-container { overflow-x: auto; margin-top: 0.5rem; }
        table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        th { text-align: left; padding: 0.75rem; background: #f9fafb; color: #6b7280; font-weight: 600; border-bottom: 1px solid #e5e7eb; }
        td { padding: 0.75rem; border-bottom: 1px solid #f3f4f6; }
        .explainer { background: #eef2ff; border-left: 4px solid #4f46e5; padding: 1rem 1.25rem; border-radius: 6px; margin-bottom: 1.5rem; font-size: 0.92rem; line-height: 1.6; }
        .explainer strong { color: #312e81; }
    </style>
</head>
<body>

<header>
    <h1>🎓 PuntoKampus — Vista del Estudiante Vendedor</h1>
    <div class="user-tag">👤 Carlos Mendoza (Enfermería, 4to sem)</div>
</header>

<div class="container">

    <div class="explainer">
        <strong>💡 ¿Cómo funciona el acceso para estudiantes?</strong><br>
        En PuntoKampus los estudiantes <strong>no tienen contraseñas</strong> (Reglas R1–R3). Acceden introduciendo su correo institucional y un código de 6 dígitos que reciben al instante. El panel <code>/admin</code> con contraseña es únicamente para los administradores y moderadores. Esta página te permite interactuar en vivo con la tienda de Carlos como si estuvieras en la app móvil.
    </div>

    @if (session('exito'))
        <div class="alert-success">
            {{ session('exito') }}
        </div>
    @endif

    <div class="grid-2">
        {{-- Tarjeta 1: Mi Tienda --}}
        <div class="card">
            <h2>
                <span>🏪 Mi Tienda</span>
                <span class="badge badge-green">{{ strtoupper($tienda->estado ?? 'activa') }}</span>
            </h2>
            <div class="info-row">
                <span>Nombre Comercial:</span>
                <strong>{{ $tienda->nombre ?? 'Sin tienda' }}</strong>
            </div>
            <div class="info-row">
                <span>Plan Actual:</span>
                <span class="badge badge-blue">Plan Gratis (Matriculado)</span>
            </div>
            <div class="info-row">
                <span>Punto de Entrega:</span>
                <strong>{{ $tienda->puntoEntrega->nombre ?? 'Biblioteca Central' }}</strong>
            </div>
            <div class="info-row">
                <span>Horario:</span>
                <span>{{ $tienda->horario ?? 'No definido' }}</span>
            </div>
            <div class="info-row">
                <span>WhatsApp de Ventas:</span>
                <span>{{ $tienda->usuario->whatsapp ?? 'Sin número' }}</span>
            </div>
            <div style="margin-top: 1rem; font-size: 0.85rem; color: #6b7280;">
                <em>"{{ $tienda->descripcion }}"</em>
            </div>
        </div>

        {{-- Tarjeta 2: Publicar Nuevo Producto --}}
        <div class="card">
            <h2>➕ Publicar Nuevo Producto</h2>
            <form method="POST" action="/demo/producto">
                @csrf
                <div class="form-group">
                    <label for="nombre">Nombre del Producto</label>
                    <input type="text" id="nombre" name="nombre" placeholder="Ej: Muffin de Vainilla" required>
                </div>
                <div class="form-group">
                    <label for="precio">Precio en dólares ($)</label>
                    <input type="number" id="precio" name="precio" step="0.05" min="0.25" max="50" placeholder="1.50" required>
                </div>
                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion" rows="2" placeholder="Detalles de preparación o ingredientes"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Publicar en Vitrina</button>
            </form>
        </div>
    </div>

    {{-- Tarjeta 3: Catálogo de Productos --}}
    <div class="card" style="margin-bottom: 1.5rem;">
        <h2>
            <span>📦 Mis Productos Publicados ({{ $productos->count() }})</span>
            <span style="font-size:0.85rem; font-weight:normal; color:#6b7280;">Visibles en el campus</span>
        </h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Descripción</th>
                        <th>Precio</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($productos as $producto)
                        <tr>
                            <td><strong>{{ $producto->nombre }}</strong></td>
                            <td style="color:#6b7280;">{{ $producto->descripcion ?? '—' }}</td>
                            <td><strong style="color:#15803d;">${{ number_format($producto->precio, 2) }}</strong></td>
                            <td><span class="badge badge-green">Disponible</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align: center; color: #9ca3af;">Aún no has publicado productos.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Tarjeta 4: Pedidos Recibidos --}}
    <div class="card">
        <h2>
            <span>🛍️ Pedidos Recibidos ({{ $pedidos->count() }})</span>
            <form method="POST" action="/demo/simular-pedido" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-secondary" style="font-size:0.8rem; padding:0.4rem 0.75rem;">Simular Nuevo Pedido Recibido</button>
            </form>
        </h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Comprador</th>
                        <th>Punto de Entrega</th>
                        <th>Total</th>
                        <th>Forma de Pago</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pedidos as $pedido)
                        <tr>
                            <td><code>{{ $pedido->codigo }}</code></td>
                            <td>
                                <strong>{{ $pedido->comprador->nombres }}</strong><br>
                                <span style="font-size:0.75rem; color:#6b7280;">{{ $pedido->comprador->correo }}</span>
                            </td>
                            <td>{{ $pedido->puntoEntrega->nombre ?? 'Campus' }}</td>
                            <td><strong>${{ number_format($pedido->total, 2) }}</strong></td>
                            <td>{{ ucfirst($pedido->forma_pago) }}</td>
                            <td>
                                @if ($pedido->estado === 'entregado')
                                    <span class="badge badge-green">Entregado</span>
                                @elseif ($pedido->estado === 'pendiente')
                                    <span class="badge badge-amber">Pendiente</span>
                                @else
                                    <span class="badge">{{ ucfirst($pedido->estado) }}</span>
                                @endif
                            </td>
                            <td>
                                @if ($pedido->estado === 'pendiente')
                                    <form method="POST" action="/demo/pedidos/{{ $pedido->codigo }}/entregado">
                                        @csrf
                                        <button type="submit" class="btn btn-success" style="font-size:0.78rem; padding:0.35rem 0.65rem;">
                                            ✓ Entregar
                                        </button>
                                    </form>
                                @else
                                    <span style="color:#9ca3af; font-size:0.8rem;">Completado</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: #9ca3af;">No hay pedidos registrados aún.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

</body>
</html>
