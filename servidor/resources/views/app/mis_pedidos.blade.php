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
                @elseif ($pedido->estado === 'vencido')
                    <span class="badge" style="background:#fee2e2; color:#991b1b; border:1px solid #fca5a5;">Vencido</span>
                @else
                    <span class="badge badge-cancelado">{{ ucfirst($pedido->estado) }}</span>
                @endif
            </div>

            @if ($pedido->paso_hora)
                <div style="background:#fef3c7; border-left:4px solid #f59e0b; color:#92400e; padding:0.45rem 0.75rem; border-radius:6px; font-size:0.82rem; margin:0.5rem 0; font-weight:600;">
                    ⚠️ Pasó la hora de entrega
                </div>
            @endif

            @if ($pedido->estado === 'vencido')
                <div style="background:#fee2e2; border-left:4px solid #ef4444; color:#991b1b; padding:0.55rem 0.8rem; border-radius:6px; font-size:0.83rem; margin:0.5rem 0;">
                    Este pedido venció porque no se confirmó la entrega.
                </div>
            @endif

            <div style="font-size:0.98rem; font-weight:700; margin-bottom:0.35rem;">
                🏪 {{ $pedido->tienda->nombre }}
            </div>
            <div style="font-size:0.8rem; color:var(--text-muted); margin-bottom:0.5rem;">
                🗓️ Realizado el: {{ $pedido->creado_en ? $pedido->creado_en->format('d/m/Y H:i') : '' }}
            </div>

            {{-- R17 / Tramo 2: Código de entrega y QR para el comprador --}}
            @if ($pedido->estado === 'pendiente' && !empty($pedido->codigo_entrega_descifrado))
                <div style="background:#FFF8F5; border:1.5px solid #CC4E01; border-radius:10px; padding:0.85rem; margin:0.75rem 0; text-align:center;">
                    <span style="font-size:0.75rem; text-transform:uppercase; letter-spacing:1px; color:#A73C00; font-weight:700; display:block;">
                        🔑 Código de entrega
                    </span>
                    <div style="font-size:2rem; font-weight:900; letter-spacing:8px; color:#CC4E01; margin:0.3rem 0;">
                        {{ $pedido->codigo_entrega_descifrado }}
                    </div>
                    <span style="font-size:0.78rem; color:#A73C00; display:block; margin-bottom:0.6rem;">
                        Muéstrale este código al vendedor o permite que escanee tu QR al recibir tu pedido.
                    </span>
                    <button type="button" class="btn btn-secondary"
                            style="min-height:34px; font-size:0.82rem; padding:0.3rem 0.85rem; display:inline-flex; align-items:center; gap:0.35rem; width:auto; margin:0 auto;"
                            onclick="mostrarModalQr('{{ $pedido->codigo }}', '{{ $pedido->codigo_entrega_descifrado }}')">
                        📱 Ver Código QR
                    </button>
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
                <span>📍 <strong>{{ $pedido->puntoEntrega->nombre ?? 'Campus' }}</strong> • ⏰ Entrega: {{ $pedido->hora_entrega ? \Carbon\Carbon::parse($pedido->hora_entrega)->format('d/m/Y H:i') : 'No especificada' }}</span>
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

    <!-- MODAL QR DE ENTREGA (TRAMO 2) -->
    <div id="modalQrPedido" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:200; align-items:center; justify-content:center; padding:1rem;" onclick="if(event.target===this) cerrarModalQr()">
        <div style="background:white; width:100%; max-width:380px; border-radius:var(--radius); padding:1.75rem 1.5rem; text-align:center; box-shadow:var(--shadow-md);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                <h3 style="font-size:1.15rem; font-weight:800; margin:0; color:var(--text);">📱 Código QR de Entrega</h3>
                <button type="button" onclick="cerrarModalQr()" style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:var(--text-muted);">✕</button>
            </div>
            <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:1rem;">
                Muéstrale este código QR al vendedor para que lo escanee y confirme la entrega presencial en el campus.
            </p>
            <div style="background:#f9fafb; padding:1rem; border-radius:12px; display:inline-block; margin-bottom:1rem; border:1px solid #e5e7eb;">
                <img id="imgQrModal" src="" alt="Código QR" style="width:230px; height:230px; display:block; margin:0 auto;">
            </div>
            <div style="font-size:0.85rem; color:var(--text-muted); margin-bottom:0.25rem;">
                O si prefieres, dícale este código:
            </div>
            <div id="pinQrModal" style="font-size:2rem; font-weight:900; letter-spacing:6px; color:#CC4E01; margin-bottom:0.5rem;">
                0000
            </div>
            <button type="button" onclick="cerrarModalQr()" class="btn btn-secondary" style="width:100%; min-height:38px;">
                Cerrar
            </button>
        </div>
    </div>

@endsection

@section('scripts')
<script>
    function mostrarModalQr(codigo, pin) {
        document.getElementById('imgQrModal').src = '/app/pedidos/' + codigo + '/qr?formato=svg';
        document.getElementById('pinQrModal').innerText = pin;
        document.getElementById('modalQrPedido').style.display = 'flex';
    }

    function cerrarModalQr() {
        document.getElementById('modalQrPedido').style.display = 'none';
        document.getElementById('imgQrModal').src = '';
    }
</script>
@endsection
