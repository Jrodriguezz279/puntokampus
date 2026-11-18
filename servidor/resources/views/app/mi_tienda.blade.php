@extends('app.layout')

@section('titulo', 'Panel de Mi Tienda — PuntoKampus')

@section('styles')
<style>
    /* =============================================
       STORE HEADER & STATS
       ============================================= */
    .store-dashboard-header {
        background: white;
        border-radius: var(--radius);
        padding: 1.5rem;
        border: 1px solid var(--border);
        box-shadow: var(--shadow);
        margin-bottom: 1.5rem;
    }

    .store-profile-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1.25rem;
    }

    .store-identity {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .store-logo-box {
        width: 64px;
        height: 64px;
        border-radius: 14px;
        background: var(--bg);
        border: 1.5px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        font-size: 1.8rem;
        font-weight: 800;
        color: var(--primary);
        flex-shrink: 0;
    }

    .store-logo-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .store-title-group h1 {
        font-size: 1.35rem;
        font-weight: 800;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .store-kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 0.75rem;
        padding-top: 1.25rem;
        border-top: 1px solid #f3f4f6;
    }

    .kpi-card {
        background: #fafaf9;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: 0.75rem 1rem;
        text-align: center;
    }

    .kpi-num {
        font-size: 1.4rem;
        font-weight: 800;
        color: var(--primary);
        line-height: 1.2;
    }

    .kpi-label {
        font-size: 0.76rem;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* =============================================
       TABS NAVIGATION
       ============================================= */
    .tabs-bar {
        display: flex;
        gap: 0.5rem;
        border-bottom: 2px solid var(--border);
        margin-bottom: 1.5rem;
        overflow-x: auto;
        padding-bottom: 2px;
        scrollbar-width: none;
    }

    .tabs-bar::-webkit-scrollbar { display: none; }

    .tab-btn {
        background: none;
        border: none;
        padding: 0.75rem 1.25rem;
        font-size: 0.92rem;
        font-weight: 600;
        font-family: inherit;
        color: var(--text-muted);
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        border-bottom: 3px solid transparent;
        margin-bottom: -2px;
        white-space: nowrap;
        transition: all 0.15s;
    }

    .tab-btn:hover {
        color: var(--text);
    }

    .tab-btn.active {
        color: var(--primary);
        border-bottom-color: var(--primary);
    }

    .tab-badge {
        font-size: 0.72rem;
        padding: 0.15rem 0.45rem;
        border-radius: 10px;
        background: #f3f4f6;
        color: var(--text);
        font-weight: 700;
    }

    .tab-btn.active .tab-badge {
        background: var(--primary-light);
        color: var(--primary);
    }

    .tab-pane {
        display: none;
    }

    .tab-pane.active {
        display: block;
        animation: fadeIn 0.2s ease-in-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(4px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* =============================================
       PEDIDOS CARDS
       ============================================= */
    .orders-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
        gap: 1rem;
    }

    @media (max-width: 600px) {
        .orders-grid { grid-template-columns: 1fr; }
    }

    .order-card {
        background: white;
        border-radius: var(--radius);
        padding: 1.15rem;
        border: 1px solid var(--border);
        box-shadow: var(--shadow);
    }

    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }

    /* =============================================
       PRODUCTOS GRID
       ============================================= */
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 1rem;
    }

    .product-mgmt-card {
        background: white;
        border-radius: var(--radius);
        border: 1px solid var(--border);
        box-shadow: var(--shadow);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: box-shadow 0.15s, transform 0.15s;
    }

    .product-mgmt-card:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
    }

    .product-mgmt-img {
        width: 100%;
        height: 160px;
        background: #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        position: relative;
        overflow: hidden;
    }

    .product-mgmt-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .product-mgmt-body {
        padding: 1rem;
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .product-mgmt-actions {
        display: flex;
        gap: 0.5rem;
        margin-top: 0.85rem;
        padding-top: 0.75rem;
        border-top: 1px solid #f3f4f6;
    }

    /* =============================================
       FORMS
       ============================================= */
    .form-group { margin-bottom: 1rem; }
    .form-group label {
        display: block;
        font-size: 0.84rem;
        font-weight: 600;
        margin-bottom: 0.35rem;
    }
    .form-control {
        width: 100%;
        padding: 0.65rem 0.85rem;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        font-size: 0.95rem;
        font-family: inherit;
    }
</style>
@endsection

@section('content')

    @php
        $pedidosPendientes = $pedidos->where('estado', 'pendiente')->count();
        $pedidosEntregados = $pedidos->where('estado', 'entregado')->count();
    @endphp

    <!-- HEADER PRINCIPAL DE MI TIENDA -->
    <div class="store-dashboard-header">
        <div class="store-profile-row">
            <div class="store-identity">
                <div class="store-logo-box">
                    @if ($tienda->logo_url)
                        <img src="{{ $tienda->logo_url }}" alt="{{ $tienda->nombre }}"
                             onerror="this.onerror=null;this.parentElement.innerHTML='{{ mb_substr($tienda->nombre, 0, 1) }}';">
                    @else
                        {{ mb_substr($tienda->nombre, 0, 1) }}
                    @endif
                </div>
                <div class="store-title-group">
                    <h1>
                        {{ $tienda->nombre }}
                        @if ($tienda->plan === 'premium')
                            <span class="badge badge-saco10">⭐ Sacó 10</span>
                        @else
                            <span class="badge badge-matriculado">Matriculado</span>
                        @endif
                    </h1>
                    <span style="font-size:0.85rem; color:var(--text-muted); display:block; margin-top:0.2rem;">
                        📍 {{ $tienda->puntoEntrega->nombre ?? 'Campus UTB' }} • 🏷️ {{ $tienda->categoria1->nombre }}
                    </span>
                </div>
            </div>

            <!-- Botones de Acción Superior -->
            <div style="display:flex; gap:0.5rem; flex-wrap:wrap; align-items:center;">
                <button type="button" onclick="cambiarPestana('editar-tienda')" class="btn btn-primary" style="font-size:0.85rem; min-height:38px; padding:0.4rem 1rem; width:auto;">
                    ✏️ Editar Información de la Tienda
                </button>
                <a href="/app/tienda/{{ $tienda->slug }}" target="_blank" class="btn btn-secondary" style="font-size:0.85rem; min-height:38px; padding:0.4rem 0.9rem;">
                    👁️ Ver tienda pública
                </a>
                <button type="button" onclick="mostrarModalLogo()" class="btn btn-secondary" style="font-size:0.85rem; min-height:38px; padding:0.4rem 0.9rem;">
                    📷 Cambiar Logo
                </button>
            </div>
        </div>

        <!-- TRAMO 2: INTERRUPTOR RECIBIENDO PEDIDOS -->
        @if ($tienda->recibiendo_pedidos)
            <div style="background:#f0fdf4; border:1.5px solid #86efac; border-radius:var(--radius-sm); padding:0.75rem 1rem; margin-top:0.85rem; margin-bottom:1rem; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.5rem;">
                <div style="display:flex; align-items:center; gap:0.5rem;">
                    <span style="font-size:1.3rem;">🟢</span>
                    <div>
                        <strong style="color:#15803d; font-size:0.95rem;">Recibiendo pedidos activamente</strong>
                        <div style="font-size:0.8rem; color:#166534;">Los compradores pueden realizar pedidos dentro de tu horario configurado.</div>
                    </div>
                </div>
                <form method="POST" action="/app/mi-tienda/toggle-pedidos" style="margin:0;">
                    @csrf
                    <button type="submit" class="btn btn-secondary" style="background:#fee2e2; border-color:#fca5a5; color:#991b1b; font-size:0.82rem; padding:0.35rem 0.8rem; min-height:34px;">
                        ⏸️ Activar "No estoy recibiendo pedidos"
                    </button>
                </form>
            </div>
        @else
            <div style="background:#fef2f2; border:1.5px solid #fca5a5; border-radius:var(--radius-sm); padding:0.75rem 1rem; margin-top:0.85rem; margin-bottom:1rem; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.5rem;">
                <div style="display:flex; align-items:center; gap:0.5rem;">
                    <span style="font-size:1.3rem;">⏸️</span>
                    <div>
                        <strong style="color:#b91c1c; font-size:0.95rem;">Interruptor activado: No estás recibiendo pedidos</strong>
                        <div style="font-size:0.8rem; color:#991b1b;">Tu tienda aparece como pausada para nuevos pedidos. {{ $tienda->siguienteAperturaTexto() }}</div>
                    </div>
                </div>
                <form method="POST" action="/app/mi-tienda/toggle-pedidos" style="margin:0;">
                    @csrf
                    <button type="submit" class="btn btn-primary" style="background:#16a34a; border-color:#15803d; font-size:0.82rem; padding:0.35rem 0.8rem; min-height:34px;">
                        ▶️ Reanudar recepción de pedidos
                    </button>
                </form>
            </div>
        @endif

        <p style="font-size:0.9rem; color:var(--text); margin-bottom:1rem;">
            {{ $tienda->descripcion ?: 'Tienda universitaria activa en el campus de la UTB.' }}
        </p>

        <!-- KPI Stats -->
        <div class="store-kpi-grid">
            <div class="kpi-card" onclick="cambiarPestana('pedidos')" style="cursor:pointer;">
                <div class="kpi-num">{{ $pedidos->count() }}</div>
                <div class="kpi-label">Pedidos Recibidos</div>
            </div>
            <div class="kpi-card" onclick="cambiarPestana('pedidos')" style="cursor:pointer; {{ $pedidosPendientes > 0 ? 'background:#FFF8F5; border-color:#fed7aa;' : '' }}">
                <div class="kpi-num" style="{{ $pedidosPendientes > 0 ? 'color:#ea580c;' : '' }}">{{ $pedidosPendientes }}</div>
                <div class="kpi-label">Pendientes</div>
            </div>
            <div class="kpi-card" onclick="cambiarPestana('productos')" style="cursor:pointer;">
                <div class="kpi-num">{{ $productos->count() }}</div>
                <div class="kpi-label">Productos Activos</div>
            </div>
            <div class="kpi-card" onclick="cambiarPestana('premium')" style="cursor:pointer;">
                <div class="kpi-num" style="font-size:1.15rem; color: {{ $tienda->plan === 'premium' ? '#d97706' : '#4338ca' }};">
                    {{ $tienda->plan === 'premium' ? 'Sacó 10 ⭐' : 'Matriculado' }}
                </div>
                <div class="kpi-label">Plan Actual</div>
            </div>
        </div>
    </div>

    <!-- PESTAÑAS DE NAVEGACIÓN ORGANIZADA -->
    <div class="tabs-bar">
        <button type="button" class="tab-btn active" id="btn-tab-pedidos" onclick="cambiarPestana('pedidos')">
            📦 Pedidos Recibidos
            <span class="tab-badge" style="{{ $pedidosPendientes > 0 ? 'background:#ea580c; color:white;' : '' }}">
                {{ $pedidos->count() }}
            </span>
        </button>
        <button type="button" class="tab-btn" id="btn-tab-productos" onclick="cambiarPestana('productos')">
            🍪 Mis Productos
            <span class="tab-badge">{{ $productos->count() }}</span>
        </button>
        <button type="button" class="tab-btn" id="btn-tab-nuevo" onclick="cambiarPestana('nuevo')">
            ➕ Publicar Producto
        </button>
        <button type="button" class="tab-btn" id="btn-tab-editar-tienda" onclick="cambiarPestana('editar-tienda')">
            ⚙️ Editar Tienda
        </button>
        <button type="button" class="tab-btn" id="btn-tab-premium" onclick="cambiarPestana('premium')">
            ⭐ Plan Premium Sacó 10
        </button>
    </div>

    <!-- ========================================================
         PESTAÑA 1: PEDIDOS RECIBIDOS
         ======================================================== -->
    <div id="pane-pedidos" class="tab-pane active">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; flex-wrap:wrap; gap:0.5rem;">
            <div>
                <h2 style="font-size:1.15rem; font-weight:800; margin:0;">Pedidos de Compradores</h2>
                <span style="font-size:0.82rem; color:var(--text-muted);">
                    Los pedidos pendientes requieren el código de 4 dígitos dado por el comprador para confirmarse.
                </span>
            </div>
            @if ($pedidosPendientes > 0)
                <span style="background:#FFF8F5; border:1px solid #fed7aa; color:#A73C00; font-size:0.8rem; font-weight:700; padding:0.3rem 0.6rem; border-radius:6px;">
                    ⚠️ Tienes {{ $pedidosPendientes }} pedido(s) por entregar
                </span>
            @endif
        </div>

        <div class="orders-grid">
            @forelse ($pedidos as $pedido)
                <div class="order-card" style="{{ $pedido->estado === 'pendiente' ? 'border-left: 4px solid var(--primary);' : '' }}">
                    <div class="order-header">
                        <div>
                            <strong style="color:var(--primary); font-size:1.05rem;">#{{ $pedido->codigo }}</strong>
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

                    <div style="font-size:0.9rem; margin:0.4rem 0;">
                        👤 <strong>{{ $pedido->comprador->nombres }}</strong>
                        <span style="color:var(--text-muted); font-size:0.82rem;">({{ $pedido->comprador->correo }})</span>
                    </div>

                    <div style="background:#f9fafb; padding:0.75rem 0.85rem; border-radius:var(--radius-sm); font-size:0.88rem; margin:0.5rem 0; border:1px solid #f3f4f6;">
                        @foreach ($pedido->items as $item)
                            <div style="display:flex; justify-content:space-between; margin-bottom:0.25rem;">
                                <span>{{ $item->cantidad }}x {{ $item->nombre_producto }}</span>
                                <strong>${{ number_format($item->subtotal, 2) }}</strong>
                            </div>
                        @endforeach
                        <div style="border-top:1px dashed #e5e7eb; margin-top:0.4rem; padding-top:0.4rem; display:flex; justify-content:space-between; font-weight:700;">
                            <span>Total a cobrar:</span>
                            <span style="color:#16a34a; font-size:1rem;">${{ number_format($pedido->total, 2) }} ({{ ucfirst($pedido->forma_pago) }})</span>
                        </div>
                    </div>

                    <div style="font-size:0.82rem; color:var(--text-muted); margin-bottom:0.75rem;">
                        📍 Entrega: <strong>{{ $pedido->puntoEntrega->nombre ?? 'Campus' }}</strong> • ⏰ {{ date('H:i', strtotime($pedido->hora_entrega)) }}
                        @if ($pedido->nota)
                            <br>💬 <em>"{{ $pedido->nota }}"</em>
                        @endif
                    </div>

                    @if ($pedido->estado === 'pendiente')
                        <div style="background:#FFF8F5; border:1.5px solid #fed7aa; border-radius:var(--radius-sm); padding:0.85rem; margin-top:0.6rem;">
                            <div style="font-size:0.82rem; font-weight:700; color:#A73C00; margin-bottom:0.4rem;">
                                🔑 Confirmar entrega con código del comprador:
                            </div>
                            @if ($pedido->codigo_bloqueado)
                                <div style="font-size:0.82rem; color:#dc2626; font-weight:600; padding:0.3rem 0;">
                                    ⚠️ Código bloqueado tras 5 intentos fallidos. Contacta al administrador para desbloquear.
                                </div>
                            @else
                                <form method="POST" action="/app/mi-tienda/pedidos/{{ $pedido->codigo }}/confirmar-entrega" style="display:flex; gap:0.5rem; align-items:center; margin-bottom:0.5rem;">
                                    @csrf
                                    <input type="text" name="codigo_entrega" maxlength="4" pattern="[0-9]{4}" placeholder="0000" required
                                           style="width:95px; text-align:center; font-size:1.25rem; font-weight:800; letter-spacing:5px; padding:0.4rem; border:1.5px solid var(--border); border-radius:8px; font-family:inherit;"
                                           title="Código de 4 dígitos dado por el comprador">
                                    <button type="submit" class="btn btn-primary" style="background:#16a34a; flex:1; min-height:42px; font-size:0.88rem;">
                                        ✓ Confirmar entrega
                                    </button>
                                </form>
                            @endif
                            <form method="POST" action="/app/mi-tienda/pedidos/{{ $pedido->codigo }}/cancelar">
                                @csrf
                                <button type="submit" class="btn btn-secondary" style="width:100%; min-height:30px; font-size:0.78rem; color:#dc2626; padding:0.25rem;" onclick="return confirm('¿Estás seguro de cancelar este pedido?');">
                                    ✕ Cancelar pedido
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @empty
                <div class="card" style="text-align:center; color:var(--text-muted); padding:3.5rem 1.5rem; grid-column:1/-1;">
                    <div style="font-size:3rem; margin-bottom:0.5rem;">📦</div>
                    <h3 style="font-size:1.15rem; color:var(--text);">Aún no tienes pedidos recibidos</h3>
                    <p style="font-size:0.88rem; margin:0.3rem 0 1rem;">Cuando los compradores hagan un pedido en tu vitrina, aparecerá aquí inmediatamente.</p>
                    <a href="/app/tienda/{{ $tienda->slug }}" target="_blank" class="btn btn-secondary" style="display:inline-flex; width:auto;">
                        Ver cómo ven mi tienda los estudiantes
                    </a>
                </div>
            @endforelse
        </div>
    </div>

    <!-- ========================================================
         PESTAÑA 2: MIS PRODUCTOS (CATÁLOGO)
         ======================================================== -->
    <div id="pane-productos" class="tab-pane">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem; flex-wrap:wrap; gap:0.5rem;">
            <div>
                <h2 style="font-size:1.15rem; font-weight:800; margin:0;">Catálogo de Productos ({{ $productos->count() }})</h2>
                <span style="font-size:0.82rem; color:var(--text-muted);">Edita precios, fotos o descripciones en cualquier momento.</span>
            </div>
            <button type="button" class="btn btn-primary" style="width:auto; min-height:38px; padding:0.4rem 1.1rem; font-size:0.88rem;" onclick="cambiarPestana('nuevo')">
                ➕ Publicar Nuevo Producto
            </button>
        </div>

        @if ($productos->isEmpty())
            <div class="card" style="text-align:center; color:var(--text-muted); padding:3.5rem 1.5rem;">
                <div style="font-size:3rem; margin-bottom:0.5rem;">🍪</div>
                <h3 style="font-size:1.15rem; color:var(--text);">Tu tienda no tiene productos publicados</h3>
                <p style="font-size:0.88rem; margin:0.3rem 0 1.25rem;">Comienza publicando los productos o servicios que ofrecerás a los estudiantes.</p>
                <button type="button" class="btn btn-primary" style="display:inline-flex; width:auto; padding:0.55rem 1.5rem;" onclick="cambiarPestana('nuevo')">
                    ➕ Publicar mi primer producto
                </button>
            </div>
        @else
            <div class="products-grid">
                @foreach ($productos as $prod)
                    <div class="product-mgmt-card">
                        <div class="product-mgmt-img">
                            @if ($prod->fotos->isNotEmpty() && $prod->fotos->first()->url)
                                <img src="{{ $prod->fotos->first()->url }}" alt="{{ $prod->nombre }}"
                                     onerror="this.onerror=null;this.parentElement.innerHTML='🍪';">
                            @else
                                🍪
                            @endif
                            <div style="position:absolute; top:8px; right:8px; display:flex; gap:0.25rem;">
                                @if ($prod->tiene_descuento)
                                    <span style="background:#ba1a1a; color:white; font-size:0.75rem; font-weight:700; padding:0.2rem 0.5rem; border-radius:6px;">
                                        -{{ round($prod->descuento_porcentaje) }}%
                                    </span>
                                    <span style="background:rgba(0,0,0,0.7); color:white; font-size:0.75rem; font-weight:700; padding:0.2rem 0.5rem; border-radius:6px;">
                                        ${{ number_format($prod->precio_efectivo, 2) }}
                                    </span>
                                @else
                                    <span style="background:rgba(0,0,0,0.7); color:white; font-size:0.75rem; font-weight:700; padding:0.2rem 0.5rem; border-radius:6px;">
                                        ${{ number_format($prod->precio, 2) }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="product-mgmt-body">
                            <div>
                                <h3 style="font-size:1.05rem; font-weight:700; margin:0 0 0.25rem;">{{ $prod->nombre }}</h3>
                                <p style="font-size:0.82rem; color:var(--text-muted); margin:0; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
                                    {{ $prod->descripcion ?: 'Sin descripción detallada.' }}
                                </p>
                            </div>
                            <div class="product-mgmt-actions">
                                <button type="button" class="btn btn-secondary" style="flex:1; min-height:34px; font-size:0.82rem; padding:0.3rem;"
                                        onclick="abrirEditarProducto({{ $prod->id }}, '{{ addslashes($prod->nombre) }}', '{{ addslashes($prod->descripcion ?? '') }}', {{ $prod->precio }}, '{{ $prod->fotos->first()?->url ?? '' }}', {{ $prod->precio_descuento ?: 'null' }})">
                                    ✏️ Editar
                                </button>
                                <form method="POST" action="/app/mi-tienda/producto/{{ $prod->id }}/eliminar" onsubmit="return confirm('¿Eliminar {{ addslashes($prod->nombre) }}?');" style="margin:0;">
                                    @csrf
                                    <button type="submit" class="btn btn-secondary" style="min-height:34px; font-size:0.82rem; padding:0.3rem 0.65rem; color:#dc2626;" title="Eliminar producto">
                                        🗑️
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- ========================================================
         PESTAÑA 3: PUBLICAR NUEVO PRODUCTO
         ======================================================== -->
    <div id="pane-nuevo" class="tab-pane">
        <div style="max-width: 650px; margin: 0 auto;">
            <div class="card" style="padding: 1.75rem;">
                <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:1.25rem;">
                    <span style="font-size:1.5rem;">➕</span>
                    <h2 style="font-size: 1.2rem; font-weight: 800; margin:0;">Publicar Nuevo Producto</h2>
                </div>

                <form method="POST" action="/app/mi-tienda/producto" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="nombre">Nombre del Producto *</label>
                        <input type="text" id="nombre" name="nombre" placeholder="Ej: Pastel de Zanahoria, Cuaderno Universitario..." class="form-control" required maxlength="80">
                    </div>

                    <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
                        <div class="form-group" style="flex:1; min-width:130px;">
                            <label for="precio">Precio Normal ($) *</label>
                            <input type="number" id="precio" name="precio" step="0.05" min="0.25" max="50" placeholder="1.25" class="form-control" required>
                        </div>
                        <div class="form-group" style="flex:1; min-width:130px;">
                            <label for="precio_descuento">Precio Oferta ($) (opcional)</label>
                            <input type="number" id="precio_descuento" name="precio_descuento" step="0.01" min="0.01" max="50" placeholder="Ej: 1.00" class="form-control">
                            <span style="font-size:0.72rem; color:var(--text-muted);">Menor al precio normal para activar oferta.</span>
                        </div>
                        <div class="form-group" style="flex:1.5; min-width:180px;">
                            <label for="foto">Foto del Producto (opcional)</label>
                            <input type="file" id="foto" name="foto" accept="image/png,image/jpeg,image/jpg" class="form-control" style="padding:0.4rem;">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="descripcion">Descripción o ingredientes (opcional)</label>
                        <textarea id="descripcion" name="descripcion" rows="3" placeholder="Casero, recién horneado, porción generosa..." class="form-control" maxlength="300"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="min-height:44px; font-size:0.95rem;">
                        Publicar en mi Tienda 🚀
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- ========================================================
         PESTAÑA 4: PLAN PREMIUM SACÓ 10
         ======================================================== -->
    <div id="pane-premium" class="tab-pane">
        <div style="max-width: 750px; margin: 0 auto;">
            <div class="card" style="border: 1.5px solid #FCD34D; background: linear-gradient(180deg, #FFFDF5 0%, #FFFFFF 100%); padding:1.75rem;">
                <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1rem;">
                    <span style="font-size:2.2rem;">⭐</span>
                    <div>
                        <h2 style="font-size: 1.3rem; font-weight: 800; color: #92400E; margin:0;">
                            Plan Premium "Sacó 10"
                        </h2>
                        <span style="font-size:0.85rem; color:var(--text-muted);">
                            Destaca tu tienda ante toda la universidad por ${{ number_format($config->precio_premium ?? 5.00, 2) }} / semestre
                        </span>
                    </div>
                </div>

                <div style="background:white; border:1px solid #fef3c7; border-radius:var(--radius-sm); padding:1rem; margin-bottom:1.25rem;">
                    <h3 style="font-size:0.92rem; font-weight:700; color:#92400E; margin-bottom:0.5rem;">Beneficios Exclusivos:</h3>
                    <ul style="font-size: 0.88rem; line-height: 1.6; color: var(--text); padding-left: 1.25rem; margin:0;">
                        <li>Insignia dorada oficial <strong>⭐ Sacó 10</strong> visible en el catálogo y tu perfil.</li>
                        <li>Posición prioritaria destacada en búsquedas y categorías.</li>
                        <li>Recepción ilimitada de pedidos sin restricciones.</li>
                    </ul>
                </div>

                @if ($tienda->plan === 'premium')
                    <div style="background:#ecfdf5; border:1.5px solid #a7f3d0; border-radius:var(--radius-sm); padding:1.25rem; color:#065f46; text-align:center;">
                        <div style="font-size:2rem; margin-bottom:0.3rem;">🎉</div>
                        <strong style="font-size:1.05rem;">¡Tu tienda ya cuenta con el Plan Sacó 10 activo!</strong>
                        <p style="font-size:0.85rem; margin:0.3rem 0 0;">Tu insignia dorada y ventajas de visibilidad están activas para toda la UTB.</p>
                    </div>
                @elseif ($ultimaSolicitudPremium && $ultimaSolicitudPremium->estado === 'pendiente')
                    <div style="background:#fffbeb; border:1.5px solid #fde68a; border-radius:var(--radius-sm); padding:1.25rem; color:#92400e;">
                        <strong style="font-size:1rem; display:block; margin-bottom:0.3rem;">⏳ Solicitud en revisión</strong>
                        Hemos recibido tu comprobante (N° <strong>{{ $ultimaSolicitudPremium->numero_comprobante }}</strong>). Nuestro equipo de moderación lo verificará en un plazo máximo de 24 horas.
                    </div>
                @else
                    @if ($ultimaSolicitudPremium && $ultimaSolicitudPremium->estado === 'rechazada')
                        <div style="background:#fef2f2; border:1px solid #fecaca; border-radius:var(--radius-sm); padding:0.85rem; color:#991b1b; font-size:0.85rem; margin-bottom:1rem;">
                            ❌ <strong>Solicitud anterior rechazada:</strong> {{ $ultimaSolicitudPremium->motivo_rechazo ?? 'Comprobante no legible o datos no coincidentes.' }}<br>
                            Puedes corregir los datos y enviar un nuevo comprobante a continuación.
                        </div>
                    @endif

                    <!-- Datos bancarios -->
                    <div style="background:#f9fafb; border:1px solid var(--border); border-radius:var(--radius-sm); padding:1rem; font-size:0.88rem; margin-bottom:1.25rem;">
                        <div style="font-weight:700; margin-bottom:0.4rem; color:var(--primary);">🏦 Datos para Transferencia o Depósito:</div>
                        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:0.5rem; margin-top:0.5rem;">
                            <div><strong>Banco:</strong> {{ $config->banco ?? 'Banco Pichincha' }}</div>
                            <div><strong>Tipo de Cuenta:</strong> {{ $config->tipo_cuenta ?? 'Ahorros' }}</div>
                            <div><strong>N° Cuenta:</strong> {{ $config->numero_cuenta ?? '2200123456' }}</div>
                            <div><strong>Titular:</strong> {{ $config->titular_cuenta ?? 'PuntoKampus UTB' }}</div>
                            @if ($config->usuario_deuna ?? '@puntokampus')
                                <div><strong>Deuna!:</strong> {{ $config->usuario_deuna ?? '@puntokampus' }}</div>
                            @endif
                        </div>
                    </div>

                    <form method="POST" action="/app/mi-tienda/premium" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label for="numero_comprobante">Número de Comprobante / Transacción *</label>
                            <input type="text" id="numero_comprobante" name="numero_comprobante" placeholder="Ej: 12345678" required class="form-control" maxlength="50" value="{{ old('numero_comprobante') }}">
                        </div>

                        <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
                            <div class="form-group" style="flex:1; min-width:140px;">
                                <label for="monto">Monto transferido ($) *</label>
                                <input type="number" id="monto" name="monto" step="0.01" min="0.01" max="1000" class="form-control" required value="{{ old('monto', $config->precio_premium ?? '5.00') }}">
                            </div>
                            <div class="form-group" style="flex:1.5; min-width:200px;">
                                <label for="foto_comprobante">Foto del Comprobante *</label>
                                <input type="file" id="foto_comprobante" name="foto_comprobante" accept="image/png,image/jpeg,image/jpg" required class="form-control" style="padding:0.4rem;">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary" style="background:#D97706; border-color:#B45309; min-height:44px; font-weight:700;">
                            ⭐ Enviar Comprobante para Activación
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- ========================================================
         PESTAÑA 5: EDITAR INFORMACIÓN DE LA TIENDA
         ======================================================== -->
    <div id="pane-editar-tienda" class="tab-pane">
        <div style="max-width: 680px; margin: 0 auto;">
            <div class="card" style="padding: 1.75rem;">
                <div style="display:flex; align-items:center; gap:0.6rem; margin-bottom:1.25rem;">
                    <span style="font-size:1.6rem;">⚙️</span>
                    <div>
                        <h2 style="font-size: 1.25rem; font-weight: 800; margin:0;">Editar Información de la Tienda</h2>
                        <span style="font-size:0.82rem; color:var(--text-muted);">Actualiza nombre, categorías, punto de entrega, contacto y horarios.</span>
                    </div>
                </div>

                <form method="POST" action="/app/mi-tienda/actualizar" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group">
                        <label for="tienda_nombre">Nombre de la Tienda *</label>
                        <input type="text" id="tienda_nombre" name="nombre" value="{{ old('nombre', $tienda->nombre) }}" required maxlength="40" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="tienda_whatsapp">WhatsApp para pedidos *</label>
                        <input type="text" id="tienda_whatsapp" name="whatsapp" value="{{ old('whatsapp', Auth::user()->whatsapp) }}" placeholder="Ej: 0991234567" required maxlength="20" class="form-control">
                        <span style="font-size:0.75rem; color:var(--text-muted);">Los compradores se comunicarán a este número para coordinar la entrega.</span>
                    </div>

                    <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
                        <div class="form-group" style="flex:1; min-width:200px;">
                            <label for="tienda_categoria_1_id">Categoría Principal *</label>
                            <select name="categoria_1_id" id="tienda_categoria_1_id" class="form-control" required>
                                @foreach ($categorias as $cat)
                                    <option value="{{ $cat->id }}" {{ old('categoria_1_id', $tienda->categoria_1_id) == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="flex:1; min-width:200px;">
                            <label for="tienda_categoria_2_id">Segunda Categoría (opcional)</label>
                            <select name="categoria_2_id" id="tienda_categoria_2_id" class="form-control">
                                <option value="">Ninguna</option>
                                @foreach ($categorias as $cat)
                                    <option value="{{ $cat->id }}" {{ old('categoria_2_id', $tienda->categoria_2_id) == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="tienda_punto_entrega_id">Punto de Entrega Habitual en el Campus *</label>
                        <select name="punto_entrega_id" id="tienda_punto_entrega_id" class="form-control">
                            @foreach ($puntos as $punto)
                                <option value="{{ $punto->id }}" {{ old('punto_entrega_id', $tienda->punto_entrega_id) == $punto->id ? 'selected' : '' }}>
                                    {{ $punto->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" style="background:#f9fafb; border:1px solid var(--border); padding:0.85rem; border-radius:var(--radius-sm);">
                        <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer; font-weight:700; margin:0;">
                            <input type="checkbox" name="recibiendo_pedidos" value="1" {{ old('recibiendo_pedidos', $tienda->recibiendo_pedidos) ? 'checked' : '' }} style="width:18px; height:18px;">
                            <span>Aceptar pedidos activamente en la tienda</span>
                        </label>
                        <span style="font-size:0.75rem; color:var(--text-muted); display:block; margin-top:0.25rem;">
                            Desmárcalo si quieres poner tu tienda en pausa ("No estoy recibiendo pedidos").
                        </span>
                    </div>

                    <div class="form-group">
                        <label for="tienda_dias_atencion">Días de Atención (opcional)</label>
                        <input type="text" id="tienda_dias_atencion" name="dias_atencion" value="{{ old('dias_atencion', $tienda->dias_atencion) }}" placeholder="Ej: Lunes a Viernes, o Lun a Sáb" class="form-control">
                    </div>

                    <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
                        <div class="form-group" style="flex:1; min-width:130px;">
                            <label for="tienda_hora_apertura">Hora Apertura (HH:MM)</label>
                            <input type="time" id="tienda_hora_apertura" name="hora_apertura" value="{{ old('hora_apertura', $tienda->hora_apertura) }}" class="form-control">
                        </div>
                        <div class="form-group" style="flex:1; min-width:130px;">
                            <label for="tienda_hora_cierre">Hora Cierre (HH:MM)</label>
                            <input type="time" id="tienda_hora_cierre" name="hora_cierre" value="{{ old('hora_cierre', $tienda->hora_cierre) }}" class="form-control">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="tienda_horario">Texto de Horario resumido (opcional)</label>
                        <input type="text" id="tienda_horario" name="horario" value="{{ old('horario', $tienda->horario) }}" placeholder="Ej: Lunes a Viernes de 10:00 a 14:00" maxlength="100" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="tienda_descripcion">Descripción de la Tienda (opcional)</label>
                        <textarea id="tienda_descripcion" name="descripcion" rows="3" maxlength="300" placeholder="¿Qué ofreces? Cuéntale a tus compañeros de la UTB..." class="form-control">{{ old('descripcion', $tienda->descripcion) }}</textarea>
                    </div>

                    <div class="form-group">
                        <label for="tienda_logo">Cambiar Logo de la Tienda (opcional)</label>
                        <input type="file" id="tienda_logo" name="logo" accept="image/png,image/jpeg,image/jpg" class="form-control" style="padding:0.4rem;">
                        <span style="font-size:0.75rem; color:var(--text-muted);">Imagen cuadrada (1:1), máx. 5MB. Si no seleccionas ningún archivo, se conserva el logo actual.</span>
                    </div>

                    <button type="submit" class="btn btn-primary" style="font-size:1rem; min-height:46px;">
                        💾 Guardar Cambios de la Tienda
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL: ACTUALIZAR LOGO DE LA TIENDA -->
    <div id="modalLogo" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:200; align-items:center; justify-content:center; padding:1rem;" onclick="if(event.target===this) cerrarModalLogo()">
        <div style="background:white; width:100%; max-width:440px; border-radius:var(--radius); padding:1.5rem; box-shadow:var(--shadow-md);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                <h3 style="font-size:1.1rem; font-weight:700; margin:0;">📷 Actualizar Logo de la Tienda</h3>
                <button type="button" onclick="cerrarModalLogo()" style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:var(--text-muted);">✕</button>
            </div>
            <form method="POST" action="/app/mi-tienda/logo" enctype="multipart/form-data">
                @csrf
                <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:1rem;">
                    Elige una imagen cuadrada (1:1, ej: 400x400 px en JPG o PNG) para que tu marca destaque en la vitrina.
                </p>
                <div class="form-group">
                    <input type="file" name="logo" accept="image/png,image/jpeg,image/jpg" required class="form-control" style="padding:0.4rem;">
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%; min-height:42px;">
                    Subir y Actualizar Logo
                </button>
            </form>
        </div>
    </div>

    <!-- MODAL: EDITAR PRODUCTO (Conserva 100% de la funcionalidad existente) -->
    <div id="modalEditarProducto" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:200; align-items:center; justify-content:center; padding:1rem;" onclick="if(event.target===this) cerrarEditarProducto()">
        <div style="background:white; width:100%; max-width:560px; border-radius:var(--radius); padding:1.5rem; max-height:88vh; overflow-y:auto; box-shadow:var(--shadow-md);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                <h3 style="font-size:1.15rem; font-weight:700; margin:0;">✏️ Editar Producto</h3>
                <button type="button" onclick="cerrarEditarProducto()" style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:var(--text-muted);">✕</button>
            </div>
            <form id="formEditarProducto" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label for="edit_nombre">Nombre del Producto *</label>
                    <input type="text" id="edit_nombre" name="nombre" class="form-control" required maxlength="80">
                </div>
                <div class="form-group">
                    <label for="edit_descripcion">Descripción (opcional)</label>
                    <input type="text" id="edit_descripcion" name="descripcion" class="form-control" maxlength="300">
                </div>
                <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
                    <div class="form-group" style="flex:1; min-width:120px;">
                        <label for="edit_precio">Precio ($) *</label>
                        <input type="number" id="edit_precio" name="precio" step="0.05" min="0.25" max="50" class="form-control" required>
                    </div>
                    <div class="form-group" style="flex:1; min-width:120px;">
                        <label for="edit_precio_descuento">Precio Oferta ($)</label>
                        <input type="number" id="edit_precio_descuento" name="precio_descuento" step="0.01" min="0.01" max="50" placeholder="Opcional" class="form-control">
                    </div>
                    <div class="form-group" style="flex:1.5; min-width:180px;">
                        <label for="edit_foto">Cambiar Foto (opcional)</label>
                        <input type="file" id="edit_foto" name="foto" accept="image/png,image/jpeg,image/jpg" class="form-control" style="padding:0.4rem;">
                    </div>
                </div>
                <!-- Vista previa de la foto actual -->
                <div id="edit_foto_preview" style="display:none; align-items:center; gap:0.6rem; margin-bottom:0.75rem;">
                    <img id="edit_foto_img" src="" alt="Foto actual" style="width:70px; height:70px; object-fit:cover; border-radius:var(--radius-sm); border:1px solid var(--border);">
                    <span style="font-size:0.78rem; color:var(--text-muted);">Foto actual (selecciona un archivo arriba para reemplazarla)</span>
                </div>
                <button type="submit" class="btn btn-primary" style="min-height:42px; width:100%;">
                    💾 Guardar Cambios
                </button>
            </form>
        </div>
    </div>

@endsection

@section('scripts')
<script>
    // Control de pestañas
    function cambiarPestana(id) {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));

        const btn = document.getElementById('btn-tab-' + id);
        const pane = document.getElementById('pane-' + id);

        if (btn) btn.classList.add('active');
        if (pane) pane.classList.add('active');

        // Actualizar URL hash sin salto brusco
        if (history.replaceState) {
            history.replaceState(null, null, '#' + id);
        }
    }

    // Modal Logo
    function mostrarModalLogo() {
        document.getElementById('modalLogo').style.display = 'flex';
    }
    function cerrarModalLogo() {
        document.getElementById('modalLogo').style.display = 'none';
    }

    // Modal Editar Producto
    function abrirEditarProducto(id, nombre, descripcion, precio, fotoUrl, precioDescuento = null) {
        document.getElementById('formEditarProducto').action = '/app/mi-tienda/producto/' + id;
        document.getElementById('edit_nombre').value = nombre;
        document.getElementById('edit_descripcion').value = descripcion;
        document.getElementById('edit_precio').value = precio;
        document.getElementById('edit_precio_descuento').value = (precioDescuento && precioDescuento > 0) ? precioDescuento : '';

        const preview = document.getElementById('edit_foto_preview');
        const img = document.getElementById('edit_foto_img');
        if (fotoUrl && fotoUrl.trim() !== '') {
            img.src = fotoUrl;
            preview.style.display = 'flex';
        } else {
            preview.style.display = 'none';
        }

        document.getElementById('edit_foto').value = '';
        document.getElementById('modalEditarProducto').style.display = 'flex';
    }

    function cerrarEditarProducto() {
        document.getElementById('modalEditarProducto').style.display = 'none';
    }

    // Activar pestaña según hash en la URL al cargar
    window.addEventListener('DOMContentLoaded', () => {
        const hash = window.location.hash.replace('#', '');
        if (['pedidos', 'productos', 'nuevo', 'premium', 'editar-tienda'].includes(hash)) {
            cambiarPestana(hash);
        }
    });
</script>
@endsection
