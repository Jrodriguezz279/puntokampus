@extends('app.layout')

@section('titulo', 'Mi Tienda — PuntoKampus')

@section('styles')
<style>
    .store-summary {
        background: white;
        border-radius: var(--radius);
        padding: 1.25rem;
        border: 1px solid var(--border);
        margin-bottom: 1.25rem;
    }

    .summary-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
    }

    .store-actions {
        display: flex;
        gap: 0.5rem;
        margin-top: 1rem;
    }

    .order-card {
        background: white;
        border-radius: var(--radius);
        padding: 1rem;
        border: 1px solid var(--border);
        margin-bottom: 0.85rem;
    }

    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }

    .form-group { margin-bottom: 0.85rem; }
    .form-group label { display: block; font-size: 0.82rem; font-weight: 600; margin-bottom: 0.3rem; }
    .form-control { width: 100%; padding: 0.6rem 0.8rem; border: 1px solid var(--border); border-radius: 8px; font-size: 0.9rem; }
</style>
@endsection

@section('content')

    <!-- Resumen de Mi Tienda -->
    <div class="store-summary">
        <div class="summary-header">
            <div>
                <h1 style="font-size: 1.25rem; font-weight: 800;">{{ $tienda->nombre }}</h1>
                <span style="font-size: 0.82rem; color: var(--text-muted);">
                    📍 {{ $tienda->puntoEntrega->nombre ?? 'Campus UTB' }} • 🏷️ {{ $tienda->categoria1->nombre }}
                </span>
            </div>
            @if ($tienda->plan === 'premium')
                <span class="badge badge-saco10">⭐ Sacó 10</span>
            @else
                <span class="badge badge-matriculado">Matriculado</span>
            @endif
        </div>

        <p style="font-size: 0.88rem; color: var(--text);">{{ $tienda->descripcion }}</p>

        <div class="store-actions">
            <a href="/app/tienda/{{ $tienda->slug }}" class="btn btn-secondary" style="flex:1; min-height:40px; font-size:0.85rem;">
                👁️ Ver cómo la ven los demás
            </a>
        </div>
    </div>

    <!-- Agregar Nuevo Producto -->
    <div class="card" style="margin-bottom: 1.25rem;">
        <h2 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 0.85rem;">
            ➕ Publicar nuevo producto
        </h2>
        <form method="POST" action="/app/mi-tienda/producto" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="nombre">Nombre del Producto</label>
                <input type="text" id="nombre" name="nombre" placeholder="Ej: Pastel de Zanahoria" class="form-control" required>
            </div>

            <div style="display:flex; gap:0.75rem;">
                <div class="form-group" style="flex:1;">
                    <label for="precio">Precio ($)</label>
                    <input type="number" id="precio" name="precio" step="0.05" min="0.25" max="50" placeholder="1.25" class="form-control" required>
                </div>
                <div class="form-group" style="flex:1.5;">
                    <label for="foto">Foto del Producto (opcional)</label>
                    <input type="file" id="foto" name="foto" accept="image/*" class="form-control" style="padding:0.4rem;">
                </div>
            </div>

            <div class="form-group">
                <label for="descripcion">Descripción o ingredientes</label>
                <input type="text" id="descripcion" name="descripcion" placeholder="Casero, recién horneado..." class="form-control">
            </div>

            <button type="submit" class="btn btn-primary" style="min-height:42px;">
                Publicar en mi Tienda
            </button>
        </form>
    </div>

    <!-- Pestañas de Gestión -->
    <h2 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 0.85rem;">
        📦 Pedidos recibidos de compradores ({{ $pedidos->count() }})
    </h2>

    @forelse ($pedidos as $pedido)
        <div class="order-card">
            <div class="order-header">
                <div>
                    <strong style="color:var(--primary); font-size:1rem;">{{ $pedido->codigo }}</strong>
                    <span style="font-size:0.8rem; color:var(--text-muted); margin-left:0.4rem;">
                        {{ $pedido->creado_en ? $pedido->creado_en->format('d/m H:i') : '' }}
                    </span>
                </div>
                @if ($pedido->estado === 'entregado')
                    <span class="badge badge-entregado">Entregado</span>
                @elseif ($pedido->estado === 'pendiente')
                    <span class="badge badge-pendiente">Pendiente</span>
                @else
                    <span class="badge badge-cancelado">{{ ucfirst($pedido->estado) }}</span>
                @endif
            </div>

            <div style="font-size:0.88rem; margin:0.4rem 0;">
                👤 <strong>{{ $pedido->comprador->nombres }}</strong> ({{ $pedido->comprador->correo }})
            </div>

            <div style="background:#f9fafb; padding:0.65rem 0.8rem; border-radius:8px; font-size:0.85rem; margin:0.5rem 0;">
                @foreach ($pedido->items as $item)
                    <div style="display:flex; justify-content:space-between;">
                        <span>{{ $item->cantidad }}x {{ $item->nombre_producto }}</span>
                        <strong>${{ number_format($item->subtotal, 2) }}</strong>
                    </div>
                @endforeach
                <div style="border-top:1px dashed #e5e7eb; margin-top:0.4rem; padding-top:0.4rem; display:flex; justify-content:space-between; font-weight:700;">
                    <span>Total:</span>
                    <span style="color:#16a34a;">${{ number_format($pedido->total, 2) }} ({{ ucfirst($pedido->forma_pago) }})</span>
                </div>
            </div>

            <div style="font-size:0.8rem; color:var(--text-muted); margin-bottom:0.75rem;">
                📍 Entrega: <strong>{{ $pedido->puntoEntrega->nombre ?? 'Campus' }}</strong> • ⏰ {{ date('H:i', strtotime($pedido->hora_entrega)) }}
                @if ($pedido->nota)
                    <br>💬 <em>"{{ $pedido->nota }}"</em>
                @endif
            </div>

            @if ($pedido->estado === 'pendiente')
                <div style="display:flex; gap:0.5rem;">
                    <form method="POST" action="/app/mi-tienda/pedidos/{{ $pedido->codigo }}/entregado" style="flex:1;">
                        @csrf
                        <button type="submit" class="btn btn-primary" style="background:#16a34a; width:100%; min-height:38px; font-size:0.85rem;">
                            ✓ Marcar como Entregado
                        </button>
                    </form>
                    <form method="POST" action="/app/mi-tienda/pedidos/{{ $pedido->codigo }}/cancelar" style="flex:0.6;">
                        @csrf
                        <button type="submit" class="btn btn-secondary" style="width:100%; min-height:38px; font-size:0.85rem; color:#dc2626;">
                            ✕ Cancelar
                        </button>
                    </form>
                </div>
            @endif
        </div>
    @empty
        <div class="card" style="text-align:center; color:var(--text-muted); padding:2rem;">
            Aún no tienes pedidos recibidos.
        </div>
    @endforelse

    <!-- Mis Productos -->
    <h2 style="font-size: 1.1rem; font-weight: 700; margin: 1.5rem 0 0.85rem;">
        Mis productos a la venta ({{ $productos->count() }})
    </h2>

    @foreach ($productos as $prod)
        <div style="background:white; border-radius:10px; padding:0.85rem; border:1px solid var(--border); margin-bottom:0.6rem; display:flex; justify-content:space-between; align-items:center;">
            <div>
                <strong>{{ $prod->nombre }}</strong><br>
                <span style="font-size:0.8rem; color:var(--text-muted);">{{ $prod->descripcion ?: 'Sin descripción' }}</span>
            </div>
            <strong style="color:#16a34a; font-size:1.05rem;">${{ number_format($prod->precio, 2) }}</strong>
        </div>
    @endforeach

@endsection
