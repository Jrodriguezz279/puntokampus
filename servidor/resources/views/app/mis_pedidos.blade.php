@extends('app.layout')

@section('titulo', 'Mis Pedidos — PuntoKampus')

@section('styles')
<style>
    .order-card {
        background: white;
        border-radius: var(--radius);
        padding: 1.15rem;
        border: 1px solid var(--border);
        margin-bottom: 0.85rem;
    }

    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }

    .whatsapp-banner {
        background: #f0fdf4;
        border: 1.5px solid #86efac;
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
    }
</style>
@endsection

@section('content')

    @if (session('pedido_creado') && session('url_whatsapp_pedido'))
        <div class="whatsapp-banner">
            <div>
                <strong style="color:#166534; font-size:0.95rem; display:block;">¡Pedido enviado con éxito! 🎉</strong>
                <span style="font-size:0.8rem; color:#15803d;">Código: <strong>{{ session('ultimo_pedido_codigo') }}</strong>. Para coordinar la entrega con el vendedor, envíale el mensaje por WhatsApp.</span>
            </div>
            <a href="{{ session('url_whatsapp_pedido') }}" target="_blank" class="btn" style="background:#25D366; color:white; font-size:0.85rem; padding:0.5rem 0.9rem; flex-shrink:0;">
                Abrir WhatsApp 📲
            </a>
        </div>
    @endif

    <h1 style="font-size: 1.25rem; font-weight: 800; margin-bottom: 1rem;">
        Mis pedidos como comprador ({{ $pedidos->count() }})
    </h1>

    @forelse ($pedidos as $pedido)
        <div class="order-card">
            <div class="order-header">
                <div>
                    <strong style="color:var(--primary); font-size:1rem;">{{ $pedido->codigo }}</strong>
                    <span style="font-size:0.8rem; color:var(--text-muted); margin-left:0.3rem;">
                        {{ $pedido->creado_en ? $pedido->creado_en->format('d/m/Y H:i') : '' }}
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

            <div style="font-size:0.95rem; font-weight:700; margin-bottom:0.4rem;">
                🏪 {{ $pedido->tienda->nombre }}
            </div>

            <div style="background:#f9fafb; padding:0.65rem 0.85rem; border-radius:8px; font-size:0.85rem; margin-bottom:0.6rem;">
                @foreach ($pedido->items as $item)
                    <div style="display:flex; justify-content:space-between; margin-bottom:0.2rem;">
                        <span>{{ $item->cantidad }}x {{ $item->nombre_producto }}</span>
                        <strong>${{ number_format($item->subtotal, 2) }}</strong>
                    </div>
                @endforeach
                <div style="border-top:1px dashed #e5e7eb; margin-top:0.4rem; padding-top:0.4rem; display:flex; justify-content:space-between; font-weight:700;">
                    <span>Total pagado:</span>
                    <span style="color:#16a34a;">${{ number_format($pedido->total, 2) }}</span>
                </div>
            </div>

            <div style="font-size:0.82rem; color:var(--text-muted); display:flex; justify-content:space-between; align-items:center;">
                <span>📍 Entrega: <strong>{{ $pedido->puntoEntrega->nombre ?? 'Campus' }}</strong> ({{ date('H:i', strtotime($pedido->hora_entrega)) }})</span>
                @if ($pedido->tienda->usuario->whatsapp)
                    <a href="https://wa.me/{{ $pedido->tienda->usuario->whatsapp }}?text={{ urlencode('Hola, tengo una consulta sobre mi pedido ' . $pedido->codigo . ' en PuntoKampus.') }}"
                       target="_blank" style="color:var(--primary); font-weight:600; text-decoration:none;">
                        Chat WhatsApp
                    </a>
                @endif
            </div>
        </div>
    @empty
        <div class="card" style="text-align:center; padding:3rem 1rem; color:var(--text-muted);">
            <div style="font-size:2.5rem; margin-bottom:0.5rem;">🛍️</div>
            <h3>Aún no has hecho ningún pedido</h3>
            <p style="font-size:0.88rem; margin:0.3rem 0 1rem;">Visita la vitrina y descubre lo que preparan tus compañeros.</p>
            <a href="/app" class="btn btn-primary" style="display:inline-block; width:auto; padding:0.5rem 1.5rem;">
                Ir a la Vitrina
            </a>
        </div>
    @endforelse

@endsection
