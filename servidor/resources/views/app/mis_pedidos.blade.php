@extends('app.layout')

@section('titulo', 'Mis Pedidos — PuntoKampus')

@section('styles')
<style>
    .orders-grid {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .order-card {
        background: white;
        border-radius: var(--radius);
        padding: 1.25rem;
        border: 1px solid var(--border);
        box-shadow: var(--shadow);
        transition: box-shadow 0.15s;
    }

    .order-card:hover {
        box-shadow: var(--shadow-md);
    }

    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.6rem;
    }

    .whatsapp-banner {
        background: #f0fdf4;
        border: 1.5px solid #86efac;
        border-radius: var(--radius);
        padding: 1.15rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }

    @media (min-width: 768px) {
        .orders-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(420px, 1fr));
            gap: 1.25rem;
        }
    }
</style>
@endsection

@section('content')

    @if (session('pedido_creado') && session('url_whatsapp_pedido'))
        <div class="whatsapp-banner">
            <div>
                <strong style="color:#166534; font-size:1rem; display:block;">¡Pedido enviado con éxito! 🎉</strong>
                <span style="font-size:0.85rem; color:#15803d;">Código: <strong>{{ session('ultimo_pedido_codigo') }}</strong>. Para coordinar la entrega con el vendedor, envíale el mensaje por WhatsApp.</span>
            </div>
            <a href="{{ session('url_whatsapp_pedido') }}" target="_blank" class="btn" style="background:#25D366; color:white; font-size:0.9rem; padding:0.5rem 1.1rem; flex-shrink:0;">
                Abrir WhatsApp 📲
            </a>
        </div>
    @endif

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem; flex-wrap:wrap; gap:0.5rem;">
        <h1 style="font-size: 1.25rem; font-weight: 800; margin:0;">
            Mis pedidos como comprador ({{ $pedidos->count() }})
        </h1>
        <a href="/app" class="btn btn-secondary" style="min-height:36px; padding:0.35rem 0.85rem; font-size:0.85rem;">
            + Explorar vitrina
        </a>
    </div>

    <div class="orders-grid">
    @forelse ($pedidos as $pedido)
        <div class="order-card">
            <div class="order-header">
                <div>
                    <strong style="color:var(--primary); font-size:1.05rem;">#{{ $pedido->codigo }}</strong>
                    <span style="font-size:0.8rem; color:var(--text-muted); margin-left:0.4rem;">
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

            <div style="font-size:0.98rem; font-weight:700; margin-bottom:0.5rem;">
                🏪 {{ $pedido->tienda->nombre }}
            </div>

            {{-- R17: Código de entrega para el comprador --}}
            @if ($pedido->estado === 'pendiente' && !empty($pedido->codigo_entrega_descifrado))
                <div style="background:#FFF8F5; border:1.5px solid #CC4E01; border-radius:10px; padding:0.85rem; margin:0.75rem 0; text-align:center;">
                    <span style="font-size:0.75rem; text-transform:uppercase; letter-spacing:1px; color:#A73C00; font-weight:700; display:block;">
                        🔑 Código de entrega
                    </span>
                    <div style="font-size:2rem; font-weight:900; letter-spacing:8px; color:#CC4E01; margin:0.3rem 0;">
                        {{ $pedido->codigo_entrega_descifrado }}
                    </div>
                    <span style="font-size:0.78rem; color:#A73C00;">
                        Muéstrale o dicta este código al vendedor cuando recibas tu pedido en persona.
                    </span>
                </div>
            @endif

            <div style="background:#f9fafb; padding:0.75rem 0.9rem; border-radius:8px; font-size:0.88rem; margin-bottom:0.75rem; border:1px solid #f3f4f6;">
                @foreach ($pedido->items as $item)
                    <div style="display:flex; justify-content:space-between; margin-bottom:0.3rem;">
                        <span>{{ $item->cantidad }}x {{ $item->nombre_producto }}</span>
                        <strong>${{ number_format($item->subtotal, 2) }}</strong>
                    </div>
                @endforeach
                <div style="border-top:1px dashed #e5e7eb; margin-top:0.4rem; padding-top:0.4rem; display:flex; justify-content:space-between; font-weight:700;">
                    <span>Total:</span>
                    <span style="color:#16a34a; font-size:1rem;">${{ number_format($pedido->total, 2) }} ({{ ucfirst($pedido->forma_pago) }})</span>
                </div>
            </div>

            <div style="font-size:0.82rem; color:var(--text-muted); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.5rem;">
                <span>📍 <strong>{{ $pedido->puntoEntrega->nombre ?? 'Campus' }}</strong> (⏰ {{ date('H:i', strtotime($pedido->hora_entrega)) }})</span>
                @if ($pedido->tienda->usuario->whatsapp)
                    <a href="https://wa.me/{{ $pedido->tienda->usuario->whatsapp }}?text={{ urlencode('Hola, tengo una consulta sobre mi pedido ' . $pedido->codigo . ' en PuntoKampus.') }}"
                       target="_blank" style="color:var(--primary); font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:0.25rem;">
                        💬 WhatsApp
                    </a>
                @endif
            </div>
        </div>
    @empty
        <div class="card" style="text-align:center; padding:3.5rem 1.5rem; color:var(--text-muted); grid-column:1/-1;">
            <div style="font-size:3rem; margin-bottom:0.75rem;">🛍️</div>
            <h3 style="font-size:1.15rem; color:var(--text);">Aún no has hecho ningún pedido</h3>
            <p style="font-size:0.9rem; margin:0.4rem 0 1.25rem;">Visita la vitrina y descubre lo que preparan y venden tus compañeros.</p>
            <a href="/app" class="btn btn-primary" style="display:inline-flex; width:auto; padding:0.6rem 1.75rem;">
                Ir a la Vitrina
            </a>
        </div>
    @endforelse
    </div>

@endsection
