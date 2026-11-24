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
       PRODUCTS SECTION & GRID
       ============================================= */
    .products-section {
        flex: 1.6;
        min-width: 0;
    }

    .products-section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .products-section-header h2 {
        font-size: 1.15rem;
        font-weight: 800;
        margin: 0;
    }

    .products-catalog-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
        gap: 1.15rem;
    }

    @media (max-width: 480px) {
        .products-catalog-grid {
            grid-template-columns: 1fr;
        }
    }

    .product-card {
        background: white;
        border-radius: var(--radius);
        border: 1px solid var(--border);
        box-shadow: var(--shadow);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: transform 0.15s, box-shadow 0.15s;
    }

    .product-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .product-photo {
        width: 100%;
        height: 170px;
        background: #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.8rem;
        color: var(--text-muted);
        position: relative;
        overflow: hidden;
    }

    .product-photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .product-price-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background: rgba(0,0,0,0.75);
        color: white;
        font-size: 0.85rem;
        font-weight: 800;
        padding: 0.25rem 0.6rem;
        border-radius: 8px;
        backdrop-filter: blur(4px);
    }

    .product-details {
        padding: 1rem;
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .product-name {
        font-size: 1.05rem;
        font-weight: 700;
        margin-bottom: 0.35rem;
        color: var(--text);
    }

    .product-desc {
        font-size: 0.84rem;
        color: var(--text-muted);
        margin-bottom: 0.85rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        line-height: 1.4;
    }

    .product-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 0.75rem;
        border-top: 1px solid #f3f4f6;
    }

    .product-price {
        font-size: 1.2rem;
        font-weight: 800;
        color: #16a34a;
    }

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

                <div style="display:flex; align-items:center; gap:0.5rem; margin-top:0.4rem; flex-wrap:wrap;">
                    @if ($tienda->estrellasPromedio())
                        <div style="display:inline-flex; align-items:center; gap:0.35rem; background:#fef3c7; border:1px solid #fde68a; padding:3px 9px; border-radius:8px;">
                            <span style="color:#f59e0b; font-size:1rem; line-height:1;">★</span>
                            <strong style="font-size:0.95rem; font-weight:800; color:#92400e;">{{ number_format($tienda->estrellasPromedio(), 1) }}</strong>
                            <span style="color:#92400e; font-size:0.8rem; font-weight:600;">({{ $tienda->estrellasTotal() }} {{ $tienda->estrellasTotal() === 1 ? 'reseña' : 'reseñas' }})</span>
                        </div>
                    @else
                        <span style="color:var(--text-muted); font-size:0.82rem; font-style:italic;">⭐ Nueva • Sin calificaciones aún</span>
                    @endif

                    @if ($tienda->estaAbiertoAhora())
                        <span style="display:inline-flex; align-items:center; gap:0.25rem; font-size:12px; font-weight:700; color:#15803d; background:#dcfce7; padding:3px 9px; border-radius:9999px; border:1px solid #bbf7d0;">
                            🟢 Abierto ahora
                        </span>
                    @elseif ($tienda->recibiendo_pedidos)
                        <span style="display:inline-flex; align-items:center; gap:0.25rem; font-size:12px; font-weight:600; color:#dc2626; background:#fee2e2; padding:3px 9px; border-radius:9999px;">
                            🔴 Cerrado ahora
                        </span>
                    @endif
                </div>

                <p style="font-size: 0.9rem; color: var(--text); margin-top: 0.4rem;">
                    {{ $tienda->descripcion ?: 'Tienda oficial de estudiante UTB.' }}
                </p>

                @if (!$tienda->recibiendo_pedidos)
                    <div style="background:#fef2f2; border:1.5px solid #fca5a5; color:#991b1b; padding:0.75rem 0.9rem; border-radius:8px; margin:0.6rem 0; font-size:0.85rem;">
                        <strong>⏸️ Tienda pausada</strong><br>
                        No está recibiendo pedidos en este momento. {{ $tienda->siguienteAperturaTexto() }}
                    </div>
                @endif

                <div class="meta-tags">
                    <span class="meta-tag">📍 {{ $tienda->puntoEntrega->nombre ?? 'Campus' }}</span>
                    @if ($tienda->dias_atencion)
                        <span class="meta-tag">📅 {{ $tienda->diasAtencionLegible() }}</span>
                    @endif
                    @if ($tienda->hora_apertura && $tienda->hora_cierre)
                        <span class="meta-tag">⏰ {{ $tienda->hora_apertura }} - {{ $tienda->hora_cierre }}</span>
                    @elseif ($tienda->horario)
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
         LEFT COLUMN (main): Products catalog grid
         ====================================================== --}}
    <section class="products-section">
        <div class="products-section-header">
            <h2>Productos disponibles ({{ $productos->count() }})</h2>
        </div>

        @if ($productos->isEmpty())
            <div class="card" style="text-align:center; color:var(--text-muted); padding:3rem 1.5rem;">
                <div style="font-size:2.5rem; margin-bottom:0.5rem;">🍪</div>
                <h3 style="color:var(--text);">Esta tienda aún no tiene productos disponibles</h3>
                <p style="font-size:0.88rem; margin-top:0.3rem;">Vuelve pronto o contacta al vendedor por WhatsApp.</p>
            </div>
        @else
            <div class="products-catalog-grid">
                @foreach ($productos as $producto)
                    <div class="product-card">
                        <div class="product-photo">
                            @if ($producto->fotos->isNotEmpty() && $producto->fotos->first()->url)
                                <img src="{{ $producto->fotos->first()->url }}" alt="{{ $producto->nombre }}"
                                     onerror="this.onerror=null;this.parentElement.innerHTML='🍪';">
                            @else
                                🍪
                            @endif
                            @if ($producto->tiene_descuento)
                                <span class="badge" style="background:#ba1a1a; color:white; font-size:0.75rem; font-weight:700; position:absolute; top:10px; left:10px; padding:3px 7px; border-radius:6px;">
                                    🔥 {{ $producto->descuento_porcentaje ?: $producto->porcentaje_descuento }}% OFF
                                </span>
                                <span class="product-price-badge" style="background:rgba(186,26,26,0.9);">
                                    ${{ number_format($producto->precio_efectivo, 2) }}
                                </span>
                            @else
                                <span class="product-price-badge">${{ number_format($producto->precio, 2) }}</span>
                            @endif
                        </div>
                        <div class="product-details">
                            <div>
                                <h3 class="product-name">{{ $producto->nombre }}</h3>
                                <p class="product-desc">{{ $producto->descripcion ?: 'Producto fresco y preparado para la comunidad de la UTB.' }}</p>
                            </div>
                            <div class="product-footer">
                                <div>
                                    @if ($producto->tiene_descuento)
                                        <s style="font-size:0.85rem; color:var(--text-muted); display:block;">${{ number_format($producto->precio, 2) }}</s>
                                        <span class="product-price" style="color:#ba1a1a;">${{ number_format($producto->precio_efectivo, 2) }}</span>
                                    @else
                                        <span class="product-price">${{ number_format($producto->precio, 2) }}</span>
                                    @endif
                                </div>
                                @auth
                                    @if (!$tienda->recibiendo_pedidos)
                                        <button type="button"
                                                class="btn btn-secondary"
                                                style="min-height:36px; padding:0.35rem 0.8rem; font-size:0.82rem; opacity:0.6; cursor:not-allowed;"
                                                title="La tienda no está recibiendo pedidos" disabled>
                                            Pausada ⏸️
                                        </button>
                                    @else
                                        <button type="button"
                                                class="btn btn-primary"
                                                style="min-height:36px; padding:0.35rem 1rem; font-size:0.88rem; width:auto;"
                                                onclick="abrirPedido({{ $producto->id }}, '{{ addslashes($producto->nombre) }}', {{ $producto->precio_efectivo }}, {{ $producto->tiene_descuento ? 'true' : 'false' }}, {{ $producto->precio }})">
                                            Pedir 🛍️
                                        </button>
                                    @endif
                                @else
                                    <a href="/app/login" class="btn btn-secondary"
                                       style="min-height:36px; padding:0.35rem 0.85rem; font-size:0.82rem; width:auto;">
                                        Entrar para pedir
                                    </a>
                                @endauth
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
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

    function abrirPedido(id, nombre, precio, tieneDescuento = false, precioOriginal = 0) {
        document.getElementById('modalProductoId').value = id;
        let titulo = 'Pedir: ' + nombre;
        if (tieneDescuento) {
            titulo += ' (Oferta)';
        }
        document.getElementById('modalProductoNombre').innerText = titulo;
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
