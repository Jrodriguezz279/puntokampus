@extends('app.layout')

@section('titulo', $tienda->nombre . ' — PuntoKampus')

@section('styles')
<style>
    /* =============================================
       DESKTOP 2-COLUMN WRAPPER
       ============================================= */
    .tienda-layout {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    /* =============================================
       STORE HERO
       ============================================= */
    .store-hero {
        background: white;
        border-radius: var(--radius);
        overflow: hidden;
        border: 1px solid var(--border);
        box-shadow: var(--shadow);
    }

    .store-cover {
        height: 120px;
        background: linear-gradient(135deg, var(--primary) 0%, #9b59b6 100%);
        background-size: cover;
        background-position: center;
    }

    .store-hero-body {
        padding: 1.25rem;
        position: relative;
    }

    .store-hero-avatar {
        width: 70px;
        height: 70px;
        border-radius: 16px;
        background: white;
        border: 3px solid white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        position: absolute;
        top: -35px;
        left: 1.25rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        font-weight: 800;
        color: var(--primary);
        overflow: hidden;
    }

    .store-hero-avatar img { width: 100%; height: 100%; object-fit: cover; }

    .store-hero-title {
        margin-top: 2rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .meta-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin: 0.75rem 0;
        font-size: 0.85rem;
        color: var(--text-muted);
    }

    .meta-tag {
        background: #f3f4f6;
        padding: 0.25rem 0.6rem;
        border-radius: var(--radius-sm);
        font-size: 0.82rem;
    }

    /* =============================================
       PRODUCTS COLUMN
       ============================================= */
    .products-section h2 {
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 0.85rem;
    }

    .product-card {
        background: white;
        border-radius: var(--radius);
        padding: 1rem;
        border: 1px solid var(--border);
        margin-bottom: 0.85rem;
        display: flex;
        gap: 1rem;
        align-items: center;
        box-shadow: var(--shadow);
        transition: box-shadow 0.15s;
    }

    .product-card:hover { box-shadow: var(--shadow-md); }

    .product-photo {
        width: 80px;
        height: 80px;
        border-radius: var(--radius-sm);
        background: #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: var(--text-muted);
        flex-shrink: 0;
        overflow: hidden;
    }

    .product-photo img { width: 100%; height: 100%; object-fit: cover; }

    .product-details { flex: 1; min-width: 0; }
    .product-name { font-size: 1rem; font-weight: 700; margin-bottom: 0.2rem; }
    .product-desc { font-size: 0.82rem; color: var(--text-muted); margin-bottom: 0.4rem; }
    .product-price { font-size: 1.1rem; font-weight: 800; color: #16a34a; }

    /* =============================================
       MODAL CHECKOUT
       ============================================= */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
        z-index: 100;
        display: none;
        align-items: flex-end;
        justify-content: center;
    }

    .modal-sheet {
        background: white;
        width: 100%;
        max-width: 600px;
        border-radius: 20px 20px 0 0;
        padding: 1.5rem;
        max-height: 85vh;
        overflow-y: auto;
    }

    .form-group { margin-bottom: 1rem; }
    .form-group label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.35rem; }
    .form-control {
        width: 100%;
        padding: 0.65rem 0.85rem;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        font-size: 0.95rem;
        font-family: inherit;
    }

    /* =============================================
       DESKTOP — ≥ 1024px: 2 columns
       ============================================= */
    @media (min-width: 1024px) {
        .tienda-layout {
            flex-direction: row;
            align-items: flex-start;
            gap: 1.75rem;
        }

        /* Left column: products (wider) */
        .products-section {
            flex: 1.6;
            min-width: 0;
        }

        /* Right column: store info */
        .store-sidebar {
            flex: 1;
            min-width: 280px;
            position: sticky;
            top: 80px;
        }

        .store-cover {
            height: 160px;
        }
    }
</style>
@endsection

@section('content')

<div class="tienda-layout">

    {{-- ======================================================
         RIGHT COLUMN (sidebar on desktop): Store Hero / Info
         Rendered first in HTML but pushed right via flex order
         ====================================================== --}}
    <aside class="store-sidebar">
        <div class="store-hero">
            <div class="store-cover"
                 style="{{ $tienda->portada_url ? 'background-image:url('.$tienda->portada_url.')' : '' }}">
            </div>
            <div class="store-hero-body">
                <div class="store-hero-avatar">
                    @if ($tienda->logo_url)
                        <img src="{{ $tienda->logo_url }}" alt="{{ $tienda->nombre }}"
                             onerror="this.onerror=null;this.parentElement.innerHTML='{{ mb_substr($tienda->nombre, 0, 1) }}';">
                    @else
                        {{ mb_substr($tienda->nombre, 0, 1) }}
                    @endif
                </div>

                <div class="store-hero-title">
                    <h1 style="font-size: 1.3rem; font-weight: 800;">{{ $tienda->nombre }}</h1>
                    @if ($tienda->plan === 'premium')
                        <span class="badge badge-saco10">⭐ Sacó 10</span>
                    @else
                        <span class="badge badge-matriculado">Matriculado</span>
                    @endif
                </div>

                <p style="font-size: 0.9rem; color: var(--text); margin-top: 0.4rem;">
                    {{ $tienda->descripcion ?: 'Tienda oficial de estudiante UTB.' }}
                </p>

                <div class="meta-tags">
                    <span class="meta-tag">📍 {{ $tienda->puntoEntrega->nombre ?? 'Campus' }}</span>
                    @if ($tienda->horario)
                        <span class="meta-tag">⏰ {{ $tienda->horario }}</span>
                    @endif
                    <span class="meta-tag">🏷️ {{ $tienda->categoria1->nombre }}</span>
                    @if ($tienda->categoria2)
                        <span class="meta-tag">🏷️ {{ $tienda->categoria2->nombre }}</span>
                    @endif
                </div>

                <div style="display:flex; gap:0.5rem; flex-wrap:wrap; margin-top:0.4rem;">
                    @if ($tienda->usuario->whatsapp)
                        <a href="https://wa.me/{{ $tienda->usuario->whatsapp }}" target="_blank"
                           class="btn btn-secondary" style="font-size: 0.85rem; padding: 0.4rem 0.8rem; min-height:36px;">
                            💬 Contactar por WhatsApp
                        </a>
                    @endif
                    @auth
                        @if (Auth::id() !== $tienda->usuario_id)
                            <button type="button" onclick="abrirReporte()"
                                    class="btn btn-secondary"
                                    style="font-size: 0.85rem; padding: 0.4rem 0.8rem; min-height:36px; color: #dc2626;">
                                🚩 Reportar tienda
                            </button>
                        @endif
                    @else
                        <a href="/app/login" class="btn btn-secondary"
                           style="font-size: 0.85rem; padding: 0.4rem 0.8rem; min-height:36px; color: var(--text-muted);">
                            🚩 Reportar tienda
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </aside>

    {{-- ======================================================
         LEFT COLUMN (main): Products list
         ====================================================== --}}
    <section class="products-section">
        <h2>Productos disponibles ({{ $productos->count() }})</h2>

        @forelse ($productos as $producto)
            <div class="product-card">
                <div class="product-photo">
                    @if ($producto->fotos->isNotEmpty() && $producto->fotos->first()->url)
                        <img src="{{ $producto->fotos->first()->url }}" alt="{{ $producto->nombre }}"
                             onerror="this.onerror=null;this.parentElement.innerHTML='🍪';">
                    @else
                        🍪
                    @endif
                </div>
                <div class="product-details">
                    <div class="product-name">{{ $producto->nombre }}</div>
                    <div class="product-desc">{{ $producto->descripcion ?? 'Producto fresco y preparado para el campus.' }}</div>
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span class="product-price">${{ number_format($producto->precio, 2) }}</span>
                        @auth
                            <button type="button"
                                    class="btn btn-accent"
                                    style="min-height:36px; padding:0.35rem 0.85rem; font-size:0.85rem;"
                                    onclick="abrirPedido({{ $producto->id }}, '{{ addslashes($producto->nombre) }}', {{ $producto->precio }})">
                                Pedir
                            </button>
                        @else
                            <a href="/app/login" class="btn btn-secondary"
                               style="min-height:36px; padding:0.35rem 0.85rem; font-size:0.85rem;">
                                Entrar para pedir
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        @empty
            <div class="card" style="text-align:center; color:var(--text-muted); padding:2rem;">
                Esta tienda aún no tiene productos disponibles.
            </div>
        @endforelse
    </section>

</div>{{-- end .tienda-layout --}}

<!-- Modal de Checkout / Confirmación de Pedido -->
<div id="modalPedido" class="modal-overlay" onclick="if(event.target===this) cerrarPedido()">
    <div class="modal-sheet">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
            <h3 id="modalProductoNombre" style="font-size:1.15rem; font-weight:700;">Hacer Pedido</h3>
            <button type="button" onclick="cerrarPedido()"
                    style="background:none; border:none; font-size:1.5rem; cursor:pointer;">✕</button>
        </div>

        <form method="POST" action="/app/pedidos">
            @csrf
            <input type="hidden" name="tienda_id" value="{{ $tienda->id }}">
            <input type="hidden" id="modalProductoId" name="producto_id" value="">

            <div class="form-group">
                <label for="cantidad">Cantidad</label>
                <input type="number" id="cantidad" name="cantidad" value="1" min="1" max="20"
                       class="form-control" required onchange="actualizarTotal()">
            </div>

            <div class="form-group">
                <label for="punto_entrega_id">Punto de Entrega en la UTB</label>
                <select name="punto_entrega_id" id="punto_entrega_id" class="form-control" required>
                    @foreach ($puntosEntrega as $punto)
                        <option value="{{ $punto->id }}"
                            {{ $tienda->punto_entrega_id == $punto->id ? 'selected' : '' }}>
                            {{ $punto->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="hora_entrega">Día y Hora de Entrega</label>
                <input type="datetime-local" id="hora_entrega" name="hora_entrega" class="form-control" required
                       min="{{ date('Y-m-d\TH:i', strtotime('+30 minutes')) }}"
                       max="{{ date('Y-m-d\TH:i', strtotime('+7 days')) }}"
                       value="{{ date('Y-m-d\TH:i', strtotime('+1 hour')) }}">
                <span style="font-size:0.75rem; color:var(--text-muted);">
                    Elige entre 30 minutos a partir de ahora y hasta 7 días posteriores.
                </span>
            </div>

            <div class="form-group">
                <label for="forma_pago">Forma de Pago</label>
                <select name="forma_pago" id="forma_pago" class="form-control" required>
                    <option value="efectivo">Efectivo al recibir</option>
                    <option value="transferencia">Transferencia Bancaria</option>
                    <option value="deuna">Deuna!</option>
                </select>
            </div>

            <div class="form-group">
                <label for="nota">Nota para el vendedor (opcional)</label>
                <input type="text" name="nota" placeholder="Ej: Llevar cambio de $5, sin cebolla..."
                       class="form-control" maxlength="200">
            </div>

            <div style="background:#f3f4f6; padding:0.85rem; border-radius:var(--radius-sm); margin-bottom:1.25rem;
                        display:flex; justify-content:space-between; align-items:center;">
                <span style="font-weight:600;">Total a pagar:</span>
                <span id="modalTotal" style="font-size:1.25rem; font-weight:800; color:#16a34a;">$0.00</span>
            </div>

            <button type="submit" class="btn btn-primary">
                Confirmar Pedido y Abrir WhatsApp 📲
            </button>
        </form>
    </div>
</div>

<!-- Modal Reportar Tienda -->
<div id="modalReporte" class="modal-overlay" onclick="if(event.target===this) cerrarReporte()">
    <div class="modal-sheet" style="max-width: 500px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
            <h3 style="font-size:1.15rem; font-weight:700; color:#dc2626; margin:0;">🚩 Reportar tienda</h3>
            <button type="button" onclick="cerrarReporte()"
                    style="background:none; border:none; font-size:1.5rem; cursor:pointer;">✕</button>
        </div>

        <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:1rem;">
            Tu reporte es anónimo para el vendedor y será revisado por el equipo de moderación en un plazo de 24 horas.
        </p>

        <form method="POST" action="/app/tienda/{{ $tienda->slug }}/reportar">
            @csrf
            <div class="form-group">
                <label for="motivo">Motivo del reporte</label>
                <select name="motivo" id="motivo" class="form-control" required onchange="toggleDetalleReporte()">
                    <option value="">Selecciona un motivo...</option>
                    <option value="prohibido">Producto o servicio prohibido / no permitido</option>
                    <option value="no_entrego">No entregó el pedido acordado</option>
                    <option value="informacion_falsa">Información falsa o engañosa</option>
                    <option value="trato_inadecuado">Trato inadecuado o irrespetuoso</option>
                    <option value="otro">Otro motivo (especificar)</option>
                </select>
            </div>

            <div class="form-group" id="grupoDetalleReporte">
                <label for="detalle">Detalle adicional</label>
                <textarea name="detalle" id="detalle" rows="3" class="form-control" maxlength="300"
                          placeholder="Describe brevemente lo ocurrido (mín. 10 caracteres si eliges 'Otro')..."></textarea>
            </div>

            <button type="submit" class="btn btn-primary"
                    style="background:#dc2626; border-color:#b91c1c; width:100%;">
                Enviar Reporte a Moderación
            </button>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    let precioUnitarioActual = 0;

    function abrirPedido(id, nombre, precio) {
        document.getElementById('modalProductoId').value = id;
        document.getElementById('modalProductoNombre').innerText = 'Pedir: ' + nombre;
        document.getElementById('cantidad').value = 1;
        precioUnitarioActual = precio;
        actualizarTotal();
        document.getElementById('modalPedido').style.display = 'flex';
    }

    function cerrarPedido() {
        document.getElementById('modalPedido').style.display = 'none';
    }

    function actualizarTotal() {
        const cant = parseInt(document.getElementById('cantidad').value) || 1;
        const total = (precioUnitarioActual * cant).toFixed(2);
        document.getElementById('modalTotal').innerText = '$' + total;
    }

    function abrirReporte() {
        document.getElementById('modalReporte').style.display = 'flex';
    }

    function cerrarReporte() {
        document.getElementById('modalReporte').style.display = 'none';
    }

    function toggleDetalleReporte() {
        const motivo = document.getElementById('motivo').value;
        const textarea = document.getElementById('detalle');
        if (motivo === 'otro') {
            textarea.required = true;
            textarea.setAttribute('minlength', '10');
        } else {
            textarea.required = false;
            textarea.removeAttribute('minlength');
        }
    }
</script>
@endsection
