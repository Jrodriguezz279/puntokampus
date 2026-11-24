@extends('app.layout')

@section('titulo', 'Vitrina del Campus — PuntoKampus UTB')

@section('styles')
<style>
    /* =========================================================
       WARM EDITORIAL CAMPUS — VITRINA SHOWCASE
       ========================================================= */

    /* Hero / Editorial Banner */
    .editorial-hero {
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid var(--border);
    }

    .editorial-eyebrow {
        display: inline-block;
        font-size: 11px;
        font-weight: 700;
        line-height: 14px;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--accent);
        background: var(--primary-light);
        padding: 4px 10px;
        border-radius: var(--radius-pill);
        margin-bottom: 0.75rem;
    }

    .editorial-headline {
        font-size: 32px;
        font-weight: 700;
        line-height: 40px;
        letter-spacing: -0.015em;
        color: var(--text);
        margin: 0 0 0.5rem 0;
    }

    .editorial-subhead {
        font-size: 16px;
        font-weight: 400;
        line-height: 24px;
        color: var(--text-muted);
        max-width: 680px;
        margin: 0;
    }

    @media (min-width: 768px) {
        .editorial-headline {
            font-size: 40px;
            line-height: 48px;
            letter-spacing: -0.02em;
        }
    }

    /* Buscador Editorial */
    .editorial-search-wrap {
        margin-bottom: 1.25rem;
    }

    .editorial-search-form {
        position: relative;
        display: flex;
        align-items: center;
    }

    .editorial-search-input {
        width: 100%;
        height: 48px;
        padding: 0 1rem 0 2.85rem;
        border-radius: var(--radius-md);
        border: 1px solid var(--border);
        background: var(--surface);
        color: var(--text);
        font-size: 15px;
        font-family: inherit;
        outline: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .editorial-search-input::placeholder {
        color: var(--text-muted);
    }

    .editorial-search-input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(204, 78, 1, 0.15);
    }

    .editorial-search-icon {
        position: absolute;
        left: 1rem;
        font-size: 1.1rem;
        color: var(--text-muted);
        pointer-events: none;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Chips de Categorías Warm Editorial */
    .editorial-categories-row {
        display: flex;
        gap: 0.5rem;
        overflow-x: auto;
        padding-bottom: 0.5rem;
        margin-bottom: 1.75rem;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }

    .editorial-categories-row::-webkit-scrollbar { display: none; }

    .editorial-chip {
        flex-shrink: 0;
        height: 32px;
        padding: 0 14px;
        border-radius: var(--radius-pill);
        background: var(--surface);
        border: 1px solid var(--border);
        font-size: 13px;
        font-weight: 500;
        font-family: inherit;
        color: var(--text-muted);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        transition: all 0.15s ease;
        white-space: nowrap;
    }

    .editorial-chip:hover {
        background: var(--bg);
        border-color: var(--text-muted);
        color: var(--text);
    }

    .editorial-chip.active {
        background: var(--primary-light);
        border-color: var(--primary);
        color: var(--accent);
        font-weight: 600;
    }

    /* Meta Bar de Resultados */
    .editorial-meta-bar {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        margin-bottom: 1.25rem;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .editorial-meta-count {
        font-size: 18px;
        font-weight: 600;
        line-height: 24px;
        color: var(--text);
        margin: 0;
    }

    .editorial-meta-tagline {
        font-size: 13px;
        font-weight: 400;
        color: var(--text-muted);
    }

    /* =========================================================
       SHOWCASE MODULES (STORE CARDS)
       ========================================================= */
    .editorial-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    @media (min-width: 640px) {
        .editorial-grid {
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.25rem;
        }
    }

    @media (min-width: 1024px) {
        .editorial-grid {
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 1.5rem;
        }
    }

    .showcase-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 1.35rem;
        box-shadow: var(--shadow-level-1);
        text-decoration: none;
        color: inherit;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: transform 200ms ease, box-shadow 200ms ease, border-color 200ms ease;
        position: relative;
    }

    .showcase-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-level-2);
        border-color: var(--primary-light);
    }

    .showcase-header {
        display: flex;
        align-items: flex-start;
        gap: 0.9rem;
        margin-bottom: 0.85rem;
    }

    .showcase-avatar-slot {
        width: 62px;
        height: 62px;
        border-radius: 12px;
        background: var(--primary-light);
        color: var(--accent);
        border: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        font-weight: 700;
        flex-shrink: 0;
        overflow: hidden;
    }

    .showcase-avatar-slot img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .showcase-title-area {
        flex: 1;
        min-width: 0;
    }

    .showcase-name-row {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        flex-wrap: wrap;
        margin-bottom: 0.25rem;
    }

    .showcase-name {
        font-size: 17px;
        font-weight: 600;
        line-height: 22px;
        color: var(--text);
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .showcase-card:hover .showcase-name {
        color: var(--primary);
    }

    /* Insignias Warm Editorial */
    .showcase-badge-saco10 {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        height: 26px;
        padding: 0 9px;
        border-radius: var(--radius-pill);
        background: var(--primary-light);
        color: var(--accent);
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.02em;
        white-space: nowrap;
    }

    .showcase-badge-matriculado {
        display: inline-flex;
        align-items: center;
        height: 26px;
        padding: 0 9px;
        border-radius: var(--radius-pill);
        background: var(--surface-container-high, #efe6e1);
        color: var(--on-surface-variant, #594238);
        border: 1px solid var(--border);
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.02em;
        white-space: nowrap;
    }

    .showcase-category-label {
        font-size: 12px;
        font-weight: 500;
        color: var(--primary-dark);
        margin: 0;
    }

    .showcase-body {
        font-size: 14px;
        font-weight: 400;
        line-height: 20px;
        color: var(--text-muted);
        margin: 0 0 1rem 0;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .showcase-footer {
        padding-top: 0.85rem;
        border-top: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 13px;
        color: var(--text-muted);
    }

    .showcase-location {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 65%;
    }

    .showcase-cta {
        font-size: 13px;
        font-weight: 600;
        color: var(--primary);
        display: inline-flex;
        align-items: center;
        gap: 0.2rem;
        white-space: nowrap;
        transition: gap 0.15s ease;
    }

    .showcase-card:hover .showcase-cta {
        gap: 0.4rem;
    }

    /* Estado Vacío Warm Editorial */
    .editorial-empty {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 3.5rem 1.5rem;
        text-align: center;
        box-shadow: var(--shadow-level-1);
    }

    .editorial-empty-icon {
        font-size: 3.5rem;
        margin-bottom: 0.85rem;
        opacity: 0.9;
    }

    .editorial-empty-title {
        font-size: 20px;
        font-weight: 700;
        color: var(--text);
        margin: 0 0 0.5rem 0;
    }

    .editorial-empty-desc {
        font-size: 14px;
        color: var(--text-muted);
        max-width: 440px;
        margin: 0 auto 1.5rem auto;
    }

    /* =========================================================
       TRAMO 2: CARRUSEL EN LA VITRINA
       ========================================================= */
    .campus-carousel-section {
        margin-bottom: 2rem;
    }

    .campus-carousel-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.85rem;
    }

    .campus-carousel-title {
        font-size: 18px;
        font-weight: 700;
        color: var(--text);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .campus-carousel-controls {
        display: flex;
        gap: 0.4rem;
    }

    .carousel-nav-btn {
        width: 32px;
        height: 32px;
        border-radius: var(--radius-pill);
        border: 1px solid var(--border);
        background: var(--surface);
        color: var(--text);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        transition: all 0.15s ease;
    }

    .carousel-nav-btn:hover {
        background: var(--primary-light);
        border-color: var(--primary);
        color: var(--accent);
    }

    .campus-carousel-track {
        display: flex;
        gap: 1rem;
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        padding: 0.25rem 0.25rem 0.75rem 0.25rem;
        scrollbar-width: none;
        -webkit-overflow-scrolling: touch;
    }

    .campus-carousel-track::-webkit-scrollbar { display: none; }

    .carousel-slide {
        flex: 0 0 280px;
        scroll-snap-align: start;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 1.15rem;
        box-shadow: var(--shadow-level-1);
        text-decoration: none;
        color: inherit;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: transform 0.15s, box-shadow 0.15s, border-color 0.15s;
        position: relative;
    }

    @media (min-width: 640px) {
        .carousel-slide { flex: 0 0 320px; }
    }

    .carousel-slide:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-level-2);
        border-color: var(--primary);
    }

    .badge-descuento {
        background: #ba1a1a;
        color: #ffffff;
        font-size: 11px;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: var(--radius-pill);
        display: inline-flex;
        align-items: center;
    }

    .badge-pausada {
        background: #fee2e2;
        color: #991b1b;
        font-size: 11px;
        font-weight: 600;
        padding: 2px 7px;
        border-radius: var(--radius-pill);
        display: inline-flex;
        align-items: center;
    }
</style>
@endsection

@section('content')

    <!-- HERO EDITORIAL DEL CAMPUS -->
    <header class="editorial-hero">
        <span class="editorial-eyebrow">Catálogo Oficial • Campus UTB</span>
        <h1 class="editorial-headline">Vitrina de Emprendimientos</h1>
        <p class="editorial-subhead">
            Explora las iniciativas, alimentos caseros, servicios universitarios y proyectos creados por la comunidad estudiantil y docente de Babahoyo.
        </p>
    </header>

    <!-- BARRA DE BÚSQUEDA EDITORIAL -->
    <div class="editorial-search-wrap">
        <form method="GET" action="/app" class="editorial-search-form">
            <span class="editorial-search-icon">🔍</span>
            <input type="text" name="q" value="{{ $busqueda }}"
                   placeholder="Buscar por nombre, comida, dulces, papelería, servicios..."
                   class="editorial-search-input" autofocus>
            @if ($categoriaId)
                <input type="hidden" name="categoria" value="{{ $categoriaId }}">
            @endif
        </form>
    </div>

    <!-- FILTROS POR CATEGORÍA (CHIPS WARM EDITORIAL) -->
    <div class="editorial-categories-row">
        <a href="/app{{ $busqueda ? '?q='.$busqueda : '' }}"
           class="editorial-chip {{ !$categoriaId ? 'active' : '' }}">
            ✨ Todas las categorías
        </a>
        @foreach ($categorias as $cat)
            <a href="/app?categoria={{ $cat->id }}{{ $busqueda ? '&q='.$busqueda : '' }}"
               class="editorial-chip {{ $categoriaId == $cat->id ? 'active' : '' }}">
                {{ $cat->nombre }}
            </a>
        @endforeach
    </div>

    <!-- TRAMO 2: CARRUSEL EN LA VITRINA (DESTACADOS SACÓ 10 & OFERTAS) -->
    @if (($carruselTiendas && $carruselTiendas->isNotEmpty()) || ($carruselOfertas && $carruselOfertas->isNotEmpty()))
        <section class="campus-carousel-section">
            <div class="campus-carousel-header">
                <h2 class="campus-carousel-title">
                    <span>✨</span> Destacados & Ofertas del Campus
                </h2>
                <div class="campus-carousel-controls">
                    <button type="button" class="carousel-nav-btn" onclick="desplazarCarrusel(-1)" aria-label="Anterior">←</button>
                    <button type="button" class="carousel-nav-btn" onclick="desplazarCarrusel(1)" aria-label="Siguiente">→</button>
                </div>
            </div>

            <div class="campus-carousel-track" id="campusCarouselTrack">
                {{-- Tiendas Premium Destacadas --}}
                @foreach ($carruselTiendas as $cTienda)
                    <a href="/app/tienda/{{ $cTienda->slug }}" class="carousel-slide" style="border-left: 3px solid var(--primary);">
                        <div>
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.6rem;">
                                <div style="display:flex; gap:0.6rem; align-items:center;">
                                    <div style="width:44px; height:44px; border-radius:10px; background:var(--primary-light); overflow:hidden; display:flex; align-items:center; justify-content:center; font-weight:700; color:var(--accent);">
                                        @if ($cTienda->logo_url)
                                            <img src="{{ $cTienda->logo_url }}" alt="{{ $cTienda->nombre }}" style="width:100%; height:100%; object-fit:cover;">
                                        @else
                                            {{ mb_substr($cTienda->nombre, 0, 1) }}
                                        @endif
                                    </div>
                                    <div>
                                        <h3 style="font-size:15px; font-weight:700; margin:0; color:var(--text); line-height:1.2;">{{ $cTienda->nombre }}</h3>
                                        <span style="font-size:12px; color:var(--primary-dark);">🏷️ {{ $cTienda->categoria1->nombre ?? 'Campus' }}</span>
                                        @if ($cTienda->estrellasPromedio())
                                            <div style="font-size:11px; font-weight:700; color:#b45309; margin-top:2px;">
                                                ⭐ {{ number_format($cTienda->estrellasPromedio(), 1) }} ({{ $cTienda->estrellasTotal() }})
                                            </div>
                                        @else
                                            <div style="font-size:11px; color:var(--text-muted); font-style:italic; margin-top:2px;">✨ Nueva</div>
                                        @endif
                                    </div>
                                </div>
                                <span class="showcase-badge-saco10" style="height:22px; font-size:10px;">⭐ Sacó 10</span>
                            </div>
                            <p style="font-size:13px; color:var(--text-muted); margin:0 0 0.5rem 0; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
                                {{ $cTienda->descripcion ?: 'Emprendimiento universitario destacado en la UTB.' }}
                            </p>
                        </div>
                        <div style="display:flex; justify-content:space-between; align-items:center; font-size:12px; color:var(--text-muted); padding-top:0.5rem; border-top:1px solid var(--border);">
                            @if (!$cTienda->recibiendo_pedidos)
                                <span class="badge-pausada">⏸️ Pedidos pausados</span>
                            @else
                                <span style="color:#15803d; font-weight:600;">🟢 Recibiendo pedidos</span>
                            @endif
                            <span style="color:var(--primary); font-weight:600;">Ver tienda →</span>
                        </div>
                    </a>
                @endforeach

                {{-- Productos con Descuento Activo --}}
                @foreach ($carruselOfertas as $cOferta)
                    @if($cOferta->tienda)
                    <a href="/app/tienda/{{ $cOferta->tienda->slug }}" class="carousel-slide" style="border-left: 3px solid #ba1a1a;">
                        <div>
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.5rem;">
                                <span class="badge-descuento">🔥 {{ $cOferta->descuento_porcentaje ?: $cOferta->porcentaje_descuento }}% OFF</span>
                                <span style="font-size:11px; color:var(--text-muted);">{{ $cOferta->tienda->nombre }}</span>
                            </div>
                            <div style="display:flex; gap:0.6rem; align-items:center; margin-bottom:0.5rem;">
                                @if ($cOferta->fotos->isNotEmpty())
                                    <img src="{{ $cOferta->fotos->first()->url }}" alt="{{ $cOferta->nombre }}" style="width:50px; height:50px; object-fit:cover; border-radius:8px; border:1px solid var(--border);">
                                @else
                                    <div style="width:50px; height:50px; border-radius:8px; background:var(--primary-light); display:flex; align-items:center; justify-content:center; font-size:1.3rem;">🍪</div>
                                @endif
                                <div style="flex:1; min-width:0;">
                                    <h3 style="font-size:14px; font-weight:700; margin:0; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $cOferta->nombre }}</h3>
                                    <div style="margin-top:0.2rem;">
                                        <s style="font-size:12px; color:var(--text-muted);">${{ number_format($cOferta->precio, 2) }}</s>
                                        <strong style="font-size:15px; color:#ba1a1a; margin-left:0.25rem;">${{ number_format($cOferta->precio_efectivo, 2) }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div style="display:flex; justify-content:space-between; align-items:center; font-size:12px; color:var(--text-muted); padding-top:0.5rem; border-top:1px solid var(--border);">
                            <span>📍 {{ $cOferta->tienda->puntoEntrega?->nombre ?? 'Campus UTB' }}</span>
                            <span style="color:var(--primary); font-weight:600;">Pedir ahora →</span>
                        </div>
                    </a>
                    @endif
                @endforeach
            </div>
        </section>
    @endif

    <!-- BARRA DE METADATOS Y CONTEO -->
    <div class="editorial-meta-bar">
        <h2 class="editorial-meta-count">
            @if ($busqueda)
                Resultados para "{{ $busqueda }}" ({{ $tiendas->total() }})
            @else
                Iniciativas activas en el campus ({{ $tiendas->total() }})
            @endif
        </h2>
        <span class="editorial-meta-tagline">
            Entregas presenciales coordinadas dentro del campus
        </span>
    </div>

    <!-- SHOWCASE MODULES: GRID EDITORIAL DE TIENDAS -->
    <div class="editorial-grid">
        @forelse ($tiendas as $tienda)
            <a href="/app/tienda/{{ $tienda->slug }}" class="showcase-card">
                <div>
                    <div class="showcase-header">
                        <div class="showcase-avatar-slot">
                            @if ($tienda->logo_url)
                                <img src="{{ $tienda->logo_url }}" alt="{{ $tienda->nombre }}"
                                     onerror="this.onerror=null;this.parentElement.innerHTML='{{ mb_substr($tienda->nombre, 0, 1) }}';">
                            @else
                                {{ mb_substr($tienda->nombre, 0, 1) }}
                            @endif
                        </div>
                        <div class="showcase-title-area">
                            <div class="showcase-name-row">
                                <h3 class="showcase-name">{{ $tienda->nombre }}</h3>
                                @if ($tienda->plan === 'premium')
                                    <span class="showcase-badge-saco10" title="Tienda verificada Plan Sacó 10">
                                        ⭐ Sacó 10
                                    </span>
                                @else
                                    <span class="showcase-badge-matriculado">
                                        Matriculado
                                    </span>
                                @endif
                                @if (!$tienda->recibiendo_pedidos)
                                    <span class="badge-pausada" title="La tienda no está recibiendo pedidos">
                                        ⏸️ Pausada
                                    </span>
                                @endif
                            </div>
                                <p class="showcase-category-label">
                                🏷️ {{ $tienda->categoria1->nombre }}
                                @if ($tienda->dias_atencion)
                                    • {{ $tienda->diasAtencionLegible() }}
                                @endif
                            </p>
                            <div style="display:flex; align-items:center; gap:0.4rem; margin-top:0.3rem; flex-wrap:wrap;">
                                @if ($tienda->estrellasPromedio())
                                    <span style="display:inline-flex; align-items:center; gap:0.2rem; font-size:12px; font-weight:700; color:#b45309; background:#fef3c7; padding:2px 7px; border-radius:9999px;">
                                        ⭐ {{ number_format($tienda->estrellasPromedio(), 1) }}
                                        <span style="font-size:11px; font-weight:500; color:#92400e;">({{ $tienda->estrellasTotal() }})</span>
                                    </span>
                                @else
                                    <span style="font-size:11px; color:var(--text-muted); font-style:italic;">✨ Nueva</span>
                                @endif
                                @if ($tienda->estaAbiertoAhora())
                                    <span style="display:inline-flex; align-items:center; gap:0.25rem; font-size:11px; font-weight:700; color:#15803d; background:#dcfce7; padding:2px 7px; border-radius:9999px; border:1px solid #bbf7d0;">
                                        🟢 Abierto
                                    </span>
                                @elseif (!$tienda->recibiendo_pedidos)
                                    {{-- Se muestra pausada arriba --}}
                                @else
                                    <span style="display:inline-flex; align-items:center; gap:0.25rem; font-size:11px; font-weight:600; color:#dc2626; background:#fee2e2; padding:2px 7px; border-radius:9999px;">
                                        🔴 Cerrado
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <p class="showcase-body">
                        {{ $tienda->descripcion ?: 'Iniciativa y productos universitarios verificados en el campus de la Universidad Técnica de Babahoyo.' }}
                    </p>
                </div>

                <div class="showcase-footer">
                    <span class="showcase-location" title="Punto de entrega y horario">
                        📍 {{ $tienda->puntoEntrega->nombre ?? 'Campus UTB' }}
                        @if ($tienda->hora_apertura && $tienda->hora_cierre)
                            • ⏰ {{ $tienda->hora_apertura }}-{{ $tienda->hora_cierre }}
                        @endif
                    </span>
                    <span class="showcase-cta">
                        Visitar tienda <span>→</span>
                    </span>
                </div>
            </a>
        @empty
            <div class="editorial-empty" style="grid-column: 1 / -1;">
                <div class="editorial-empty-icon">🏪</div>
                <h3 class="editorial-empty-title">No encontramos tiendas con ese criterio</h3>
                <p class="editorial-empty-desc">
                    Intenta con otra palabra clave o selecciona otra categoría para explorar más emprendimientos del campus.
                </p>
                <a href="/app" class="btn btn-primary" style="display:inline-flex; width:auto; height:44px; padding:0 24px;">
                    Ver todas las tiendas
                </a>
            </div>
        @endforelse
    </div>

@endsection

@section('scripts')
<script>
    function desplazarCarrusel(dir) {
        const track = document.getElementById('campusCarouselTrack');
        if (track) {
            track.scrollBy({ left: dir * 310, behavior: 'smooth' });
        }
    }
</script>
@endsection
