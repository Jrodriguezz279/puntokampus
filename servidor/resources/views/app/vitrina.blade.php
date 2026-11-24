@extends('app.layout')

@section('titulo', 'Vitrina del Campus — PuntoKampus UTB')

@section('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
<style>
    /* =========================================================
       PUNTOKAMPUS UTB — STITCH WEB SHOWCASE (P05)
       ========================================================= */
    :root {
        --pk-primary: #9d3c00;
        --pk-primary-dark: #7c2e00;
        --pk-primary-container: #c2500f;
        --pk-primary-fixed: #ffdbcd;
        --pk-primary-light: #fff1e9;
        --pk-secondary: #a23f00;
        --pk-secondary-container: #fe762c;
        --pk-secondary-fixed: #ffdbcc;
        --pk-surface: #fff8f5;
        --pk-surface-low: #fff1e9;
        --pk-surface-container: #fdeadf;
        --pk-surface-high: #f7e5d9;
        --pk-surface-highest: #f1dfd4;
        --pk-surface-lowest: #ffffff;
        --pk-surface-dim: #e9d7cb;
        --pk-on-surface: #231a13;
        --pk-on-surface-variant: #584239;
        --pk-outline: #8b7267;
        --pk-outline-variant: #dfc0b4;
        --pk-green-bg: #dcefd5;
        --pk-green-text: #2e7d32;
        --pk-gray-bg: #e4e4e4;
        --pk-gray-text: #555555;
    }

    body {
        font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        background-color: var(--pk-surface);
        color: var(--pk-on-surface);
    }

    /* Contenedor Principal de la Vitrina */
    .stitch-vitrina-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0.5rem 0.5rem 3rem 0.5rem;
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    /* 1. Header / Hero Banner */
    .stitch-hero-banner {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
        padding-top: 0.5rem;
    }

    .stitch-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        color: var(--pk-primary);
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .stitch-eyebrow .material-symbols-outlined {
        font-size: 18px;
    }

    .stitch-headline {
        font-size: 34px;
        font-weight: 800;
        line-height: 42px;
        letter-spacing: -0.02em;
        color: var(--pk-on-surface);
        margin: 0;
    }

    .stitch-subhead {
        font-size: 16px;
        font-weight: 400;
        line-height: 24px;
        color: var(--pk-on-surface-variant);
        max-width: 720px;
        margin: 0;
    }

    /* 2. Buscador Grande */
    .stitch-search-section {
        position: relative;
        width: 100%;
    }

    .stitch-search-box {
        position: relative;
        display: flex;
        align-items: center;
        width: 100%;
        min-height: 52px;
        background: var(--pk-surface-lowest);
        border: 1px solid var(--pk-outline-variant);
        border-radius: 14px;
        box-shadow: 0 1px 4px rgba(42, 30, 22, 0.04);
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .stitch-search-box:focus-within {
        border-color: var(--pk-primary);
        box-shadow: 0 0 0 3px rgba(157, 60, 0, 0.15);
    }

    .stitch-search-icon {
        position: absolute;
        left: 1rem;
        color: var(--pk-on-surface-variant);
        font-size: 22px;
        pointer-events: none;
    }

    .stitch-search-input {
        width: 100%;
        height: 52px;
        padding: 0 3rem 0 3rem;
        background: transparent;
        border: none;
        outline: none;
        font-size: 15px;
        font-family: inherit;
        color: var(--pk-on-surface);
    }

    .stitch-search-input::placeholder {
        color: var(--pk-on-surface-variant);
        opacity: 0.7;
    }

    .stitch-search-clear {
        position: absolute;
        right: 0.75rem;
        background: transparent;
        border: none;
        color: var(--pk-on-surface-variant);
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background-color 0.15s;
    }

    .stitch-search-clear:hover {
        background: var(--pk-surface-container);
        color: var(--pk-primary);
    }

    /* 3. Chips de Categorías */
    .stitch-categories-scroll {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        overflow-x: auto;
        padding-bottom: 0.5rem;
        scrollbar-width: none;
        -webkit-overflow-scrolling: touch;
    }

    .stitch-categories-scroll::-webkit-scrollbar {
        display: none;
    }

    .stitch-cat-chip {
        flex-shrink: 0;
        height: 40px;
        padding: 0 16px;
        border-radius: 9999px;
        background: var(--pk-surface-low);
        color: var(--pk-on-surface-variant);
        border: 1px solid var(--pk-outline-variant);
        font-size: 13px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        text-decoration: none;
        transition: all 0.15s ease;
        white-space: nowrap;
    }

    .stitch-cat-chip:hover {
        background: var(--pk-surface-container);
        color: var(--pk-on-surface);
    }

    .stitch-cat-chip.active {
        background: var(--pk-secondary-container);
        border-color: var(--pk-secondary-container);
        color: #ffffff;
        box-shadow: 0 2px 6px rgba(254, 118, 44, 0.25);
    }

    /* 4. Selector Segmentado: Tiendas vs Productos */
    .stitch-segmented-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }

    .stitch-segmented-control {
        display: flex;
        background: var(--pk-surface-container);
        padding: 4px;
        border-radius: 12px;
        border: 1px solid var(--pk-outline-variant);
    }

    .stitch-segment-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 8px 20px;
        border-radius: 8px;
        border: none;
        background: transparent;
        font-size: 14px;
        font-weight: 600;
        color: var(--pk-on-surface-variant);
        cursor: pointer;
        transition: all 0.2s ease;
        font-family: inherit;
    }

    .stitch-segment-btn.active {
        background: var(--pk-surface-lowest);
        color: var(--pk-primary);
        font-weight: 700;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
    }

    .stitch-counter-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 6px 14px;
        border-radius: 9999px;
        background: var(--pk-surface-high);
        color: var(--pk-on-surface);
        font-size: 13px;
        font-weight: 600;
    }

    .stitch-counter-pill .material-symbols-outlined {
        color: var(--pk-primary);
        font-size: 18px;
    }

    /* 5. Carrusel de Destacadas & Ofertas (Hero Showcase) */
    .stitch-carousel-section {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .stitch-carousel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .stitch-carousel-title {
        font-size: 18px;
        font-weight: 700;
        color: var(--pk-on-surface);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .stitch-carousel-title .material-symbols-outlined {
        color: var(--pk-secondary);
        font-size: 22px;
    }

    .stitch-carousel-controls {
        display: flex;
        gap: 0.5rem;
    }

    .stitch-carousel-btn {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        border: 1px solid var(--pk-outline-variant);
        background: var(--pk-surface-lowest);
        color: var(--pk-on-surface);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.15s;
    }

    .stitch-carousel-btn:hover {
        background: var(--pk-primary-fixed);
        color: var(--pk-primary-dark);
        border-color: var(--pk-primary);
    }

    .stitch-carousel-track {
        display: flex;
        gap: 1.25rem;
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        padding: 0.25rem 0.25rem 0.75rem 0.25rem;
        scrollbar-width: none;
        -webkit-overflow-scrolling: touch;
    }

    .stitch-carousel-track::-webkit-scrollbar {
        display: none;
    }

    .stitch-carousel-card {
        flex: 0 0 340px;
        scroll-snap-align: start;
        background: var(--pk-surface-lowest);
        border: 1px solid var(--pk-outline-variant);
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(42, 30, 22, 0.04);
        display: flex;
        flex-direction: column;
        text-decoration: none;
        color: inherit;
        transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s;
    }

    .stitch-carousel-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 24px rgba(42, 30, 22, 0.08);
        border-color: var(--pk-primary);
    }

    .stitch-carousel-cover {
        position: relative;
        height: 140px;
        background: var(--pk-surface-dim);
        overflow: hidden;
    }

    .stitch-carousel-cover img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .stitch-carousel-cover .scrim {
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(35, 26, 19, 0.6) 0%, transparent 60%);
    }

    .stitch-carousel-badge-top {
        position: absolute;
        top: 10px;
        left: 10px;
        display: flex;
        gap: 6px;
        z-index: 2;
    }

    .stitch-badge-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 9999px;
        font-size: 11px;
        font-weight: 700;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
    }

    .stitch-badge-abierto {
        background: var(--pk-green-bg);
        color: var(--pk-green-text);
    }

    .stitch-badge-abierto .dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--pk-green-text);
        animation: stitchPulse 1.8s infinite;
    }

    @keyframes stitchPulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.4; transform: scale(0.85); }
    }

    .stitch-badge-descuento {
        background: var(--pk-secondary-container);
        color: #ffffff;
    }

    .stitch-badge-cerrado {
        background: var(--pk-gray-bg);
        color: var(--pk-gray-text);
    }

    .stitch-carousel-content {
        padding: 1rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        flex: 1;
        gap: 0.5rem;
    }

    .stitch-avatar-header {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        margin-top: -1.75rem;
        position: relative;
        z-index: 3;
    }

    .stitch-avatar-box {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        background: var(--pk-primary-fixed);
        color: var(--pk-primary-dark);
        border: 2px solid var(--pk-surface-lowest);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        font-weight: 800;
        flex-shrink: 0;
        overflow: hidden;
    }

    .stitch-avatar-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .stitch-name-col {
        flex: 1;
        min-width: 0;
        padding-top: 1.5rem;
    }

    .stitch-store-name {
        font-size: 16px;
        font-weight: 700;
        line-height: 20px;
        color: var(--pk-on-surface);
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .stitch-store-sub {
        font-size: 12px;
        color: var(--pk-on-surface-variant);
        margin-top: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Calificación con máxima jerarquía */
    .stitch-rating-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 8px;
        border-radius: 8px;
        background: #fef3c7;
        color: #b45309;
        font-size: 12px;
        font-weight: 700;
    }

    .stitch-rating-badge .material-symbols-outlined {
        font-size: 16px;
        color: #d97706;
    }

    .stitch-badge-saco10 {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        padding: 2px 8px;
        border-radius: 9999px;
        background: var(--pk-primary-fixed);
        color: var(--pk-primary-dark);
        font-size: 11px;
        font-weight: 700;
    }

    .stitch-badge-matriculado {
        display: inline-flex;
        align-items: center;
        padding: 2px 8px;
        border-radius: 9999px;
        background: var(--pk-surface-high);
        color: var(--pk-on-surface-variant);
        font-size: 11px;
        font-weight: 600;
    }

    .stitch-card-footer {
        padding-top: 0.75rem;
        border-top: 1px solid var(--pk-surface-container);
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 12px;
        color: var(--pk-on-surface-variant);
    }

    .stitch-location-tag {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 65%;
    }

    .stitch-cta-link {
        color: var(--pk-primary);
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 2px;
    }

    /* 6. Grid de Tiendas Emprendedores UTB */
    .stitch-stores-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.25rem;
    }

    @media (min-width: 768px) {
        .stitch-stores-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    .stitch-store-card {
        background: var(--pk-surface-lowest);
        border: 1px solid var(--pk-outline-variant);
        border-radius: 18px;
        padding: 1.25rem;
        box-shadow: 0 1px 4px rgba(42, 30, 22, 0.04);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 1rem;
        text-decoration: none;
        color: inherit;
        transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s;
    }

    .stitch-store-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(42, 30, 22, 0.08);
        border-color: var(--pk-primary);
    }

    .stitch-store-header-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
    }

    .stitch-store-header-left {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        min-width: 0;
    }

    .stitch-store-avatar {
        width: 50px;
        height: 50px;
        border-radius: 14px;
        background: var(--pk-secondary-fixed);
        color: var(--pk-primary-dark);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        font-weight: 800;
        flex-shrink: 0;
        overflow: hidden;
    }

    .stitch-store-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .stitch-store-details {
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .stitch-title-badges {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }

    .stitch-store-title {
        font-size: 16px;
        font-weight: 700;
        color: var(--pk-on-surface);
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .stitch-card:hover .stitch-store-title {
        color: var(--pk-primary);
    }

    .stitch-category-text {
        font-size: 12px;
        color: var(--pk-on-surface-variant);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .stitch-store-meta-tags {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
        margin-top: 0.5rem;
    }

    .stitch-store-desc {
        font-size: 13px;
        line-height: 19px;
        color: var(--pk-on-surface-variant);
        margin: 0.5rem 0 0 0;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* 7. Grid de Productos Populares en Campus */
    .stitch-products-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }

    @media (min-width: 768px) {
        .stitch-products-grid {
            grid-template-columns: repeat(3, 1fr);
            gap: 1.25rem;
        }
    }

    @media (min-width: 1024px) {
        .stitch-products-grid {
            grid-template-columns: repeat(4, 1fr);
            gap: 1.25rem;
        }
    }

    .stitch-product-card {
        background: var(--pk-surface-lowest);
        border: 1px solid var(--pk-outline-variant);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 1px 4px rgba(42, 30, 22, 0.04);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        text-decoration: none;
        color: inherit;
        transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s;
    }

    .stitch-product-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(42, 30, 22, 0.08);
        border-color: var(--pk-primary);
    }

    .stitch-product-img-box {
        position: relative;
        height: 160px;
        width: 100%;
        background: var(--pk-surface-dim);
        overflow: hidden;
    }

    .stitch-product-img-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .stitch-product-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.2rem;
        background: var(--pk-surface-low);
    }

    .stitch-product-badges {
        position: absolute;
        top: 8px;
        left: 8px;
        display: flex;
        flex-direction: column;
        gap: 4px;
        z-index: 2;
    }

    .stitch-product-body {
        padding: 0.85rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        flex: 1;
        gap: 0.5rem;
    }

    .stitch-prod-store-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 11px;
        color: var(--pk-on-surface-variant);
    }

    .stitch-product-name {
        font-size: 14px;
        font-weight: 700;
        line-height: 18px;
        color: var(--pk-on-surface);
        margin: 0;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .stitch-product-prices {
        display: flex;
        align-items: baseline;
        gap: 6px;
        margin-top: 0.25rem;
    }

    .stitch-price-final {
        font-size: 18px;
        font-weight: 800;
        color: var(--pk-primary);
    }

    .stitch-price-orig {
        font-size: 13px;
        color: var(--pk-outline);
        text-decoration: line-through;
    }

    .stitch-btn-order {
        width: 100%;
        min-height: 40px;
        margin-top: 0.4rem;
        border-radius: 10px;
        background: var(--pk-primary-fixed);
        color: var(--pk-primary-dark);
        border: none;
        font-size: 13px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        cursor: pointer;
        transition: background-color 0.15s;
        text-decoration: none;
    }

    .stitch-btn-order:hover {
        background: var(--pk-secondary-container);
        color: #ffffff;
    }

    /* Estados Vacíos */
    .stitch-empty-card {
        background: var(--pk-surface-lowest);
        border: 1px solid var(--pk-outline-variant);
        border-radius: 18px;
        padding: 3rem 1.5rem;
        text-align: center;
        box-shadow: 0 1px 4px rgba(42, 30, 22, 0.04);
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.75rem;
    }

    .stitch-empty-icon {
        font-size: 3rem;
    }

    .stitch-empty-title {
        font-size: 20px;
        font-weight: 700;
        color: var(--pk-on-surface);
        margin: 0;
    }

    .stitch-empty-desc {
        font-size: 14px;
        color: var(--pk-on-surface-variant);
        max-width: 460px;
        margin: 0;
    }

    /* Paginación */
    .stitch-pagination-wrap {
        display: flex;
        justify-content: center;
        padding-top: 1rem;
    }
</style>
@endsection

@section('content')
<div class="stitch-vitrina-container">

    <!-- 1. ENCABEZADO DE LA VITRINA (HERO EDITORIAL) -->
    <header class="stitch-hero-banner">
        <div class="stitch-eyebrow">
            <span class="material-symbols-outlined">local_mall</span>
            <span>Mercado Colaborativo Universitario • Campus UTB</span>
        </div>
        <h1 class="stitch-headline">Vitrina</h1>
        <p class="stitch-subhead">
            Descubre lo que se crea en tu campus. Compras directas, entregas presenciales y promociones exclusivas entre compañeros de la Universidad Técnica de Babahoyo.
        </p>
    </header>

    <!-- 2. BUSCADOR INTEGRADO -->
    <section class="stitch-search-section">
        <form method="GET" action="/app" class="stitch-search-box">
            <span class="material-symbols-outlined stitch-search-icon">search</span>
            <input type="text" name="q" value="{{ $busqueda }}"
                   placeholder="¿Qué estás buscando? (tiendas o productos)"
                   class="stitch-search-input" id="stitch-search-input" autocomplete="off">
            @if ($categoriaId)
                <input type="hidden" name="categoria" value="{{ $categoriaId }}">
            @endif
            @if ($busqueda)
                <a href="/app{{ $categoriaId ? '?categoria='.$categoriaId : '' }}" class="stitch-search-clear" title="Limpiar búsqueda">
                    <span class="material-symbols-outlined" style="font-size: 20px;">close</span>
                </a>
            @endif
        </form>
    </section>

    <!-- 3. CHIPS DE CATEGORÍAS (SCROLL HORIZONTAL) -->
    <section class="stitch-categories-scroll">
        <a href="/app{{ $busqueda ? '?q='.$busqueda : '' }}"
           class="stitch-cat-chip {{ !$categoriaId ? 'active' : '' }}">
            <span class="material-symbols-outlined" style="font-size: 16px;">grid_view</span>
            <span>Todas</span>
        </a>
        @foreach ($categorias as $cat)
            <a href="/app?categoria={{ $cat->id }}{{ $busqueda ? '&q='.$busqueda : '' }}"
               class="stitch-cat-chip {{ $categoriaId == $cat->id ? 'active' : '' }}">
                <span>{{ $cat->nombre }}</span>
            </a>
        @endforeach
    </section>

    <!-- 4. SELECTOR SEGMENTADO: TIENDAS VS PRODUCTOS -->
    <section class="stitch-segmented-row">
        <div class="stitch-segmented-control" role="tablist">
            <button class="stitch-segment-btn active" id="tab-btn-stores" onclick="switchVitrinaTab('stores')" type="button">
                <span class="material-symbols-outlined" style="font-size: 18px;">storefront</span>
                <span>Tiendas</span>
            </button>
            <button class="stitch-segment-btn" id="tab-btn-products" onclick="switchVitrinaTab('products')" type="button">
                <span class="material-symbols-outlined" style="font-size: 18px;">inventory_2</span>
                <span>Productos</span>
            </button>
        </div>

        <div class="stitch-counter-pill" id="tab-counter-label">
            <span class="material-symbols-outlined">verified</span>
            <span><strong>{{ $tiendas->total() }} tiendas</strong> en campus UTB</span>
        </div>
    </section>

    <!-- 5. CARRUSEL DE DESTACADAS EN CAMPUS (TIENDAS SACÓ 10 & OFERTAS) -->
    @if (($carruselTiendas && $carruselTiendas->isNotEmpty()) || ($carruselOfertas && $carruselOfertas->isNotEmpty()))
        <section class="stitch-carousel-section">
            <div class="stitch-carousel-header">
                <h2 class="stitch-carousel-title">
                    <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">local_fire_department</span>
                    <span>Destacadas en Campus</span>
                </h2>
                <div class="stitch-carousel-controls">
                    <button type="button" class="stitch-carousel-btn" onclick="desplazarStitchCarrusel(-1)" aria-label="Anterior">
                        <span class="material-symbols-outlined" style="font-size: 18px;">arrow_back</span>
                    </button>
                    <button type="button" class="stitch-carousel-btn" onclick="desplazarStitchCarrusel(1)" aria-label="Siguiente">
                        <span class="material-symbols-outlined" style="font-size: 18px;">arrow_forward</span>
                    </button>
                </div>
            </div>

            <div class="stitch-carousel-track" id="stitchCarouselTrack">
                {{-- Tiendas Premium / Destacadas --}}
                @foreach ($carruselTiendas as $cTienda)
                    <a href="/app/tienda/{{ $cTienda->slug }}" class="stitch-carousel-card">
                        <div class="stitch-carousel-cover">
                            @if ($cTienda->logo_url)
                                <img src="{{ $cTienda->logo_url }}" alt="{{ $cTienda->nombre }}" loading="lazy">
                            @else
                                <div style="width: 100%; height: 100%; background: linear-gradient(135deg, var(--pk-primary-container) 0%, var(--pk-secondary) 100%);"></div>
                            @endif
                            <div class="scrim"></div>

                            <div class="stitch-carousel-badge-top">
                                @if ($cTienda->estaAbiertoAhora())
                                    <span class="stitch-badge-pill stitch-badge-abierto">
                                        <span class="dot"></span>
                                        <span>Abierto</span>
                                    </span>
                                @elseif (!$cTienda->recibiendo_pedidos)
                                    <span class="stitch-badge-pill stitch-badge-cerrado">
                                        <span>Pausada</span>
                                    </span>
                                @else
                                    <span class="stitch-badge-pill stitch-badge-cerrado">
                                        <span>Cerrado</span>
                                    </span>
                                @endif

                                @if ($cTienda->plan === 'premium')
                                    <span class="stitch-badge-pill stitch-badge-saco10">
                                        <span>★ Sacó 10</span>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="stitch-carousel-content">
                            <div class="stitch-avatar-header">
                                <div class="stitch-avatar-box">
                                    @if ($cTienda->logo_url)
                                        <img src="{{ $cTienda->logo_url }}" alt="{{ $cTienda->nombre }}"
                                             onerror="this.onerror=null;this.parentElement.innerHTML='{{ mb_substr($cTienda->nombre, 0, 1) }}';">
                                    @else
                                        {{ mb_substr($cTienda->nombre, 0, 1) }}
                                    @endif
                                </div>
                                <div class="stitch-name-col">
                                    <h3 class="stitch-store-name">{{ $cTienda->nombre }}</h3>
                                    <div class="stitch-store-sub">🏷️ {{ $cTienda->categoria1->nombre ?? 'Campus UTB' }}</div>
                                </div>
                            </div>

                            <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                @if ($cTienda->estrellasPromedio())
                                    <span class="stitch-rating-badge">
                                        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">star</span>
                                        <span>{{ number_format($cTienda->estrellasPromedio(), 1) }}</span>
                                        <span style="font-size: 11px; opacity: 0.85;">({{ $cTienda->estrellasTotal() }})</span>
                                    </span>
                                @else
                                    <span class="stitch-badge-matriculado">✨ Nueva</span>
                                @endif

                                @if ($cTienda->productos->where('descuento_activo', true)->count() > 0)
                                    <span class="stitch-badge-pill stitch-badge-descuento">🔥 Ofertas hoy</span>
                                @endif
                            </div>

                            <p class="stitch-store-desc">
                                {{ $cTienda->descripcion ?: 'Emprendimiento verificado de la comunidad estudiantil de Babahoyo.' }}
                            </p>

                            <div class="stitch-card-footer">
                                <span class="stitch-location-tag" title="Punto de entrega">
                                    <span class="material-symbols-outlined" style="font-size: 15px; color: var(--pk-primary);">pin_drop</span>
                                    <span>{{ $cTienda->puntoEntrega?->nombre ?? 'Campus UTB' }}</span>
                                </span>
                                <span class="stitch-cta-link">
                                    <span>Ver vitrina</span>
                                    <span class="material-symbols-outlined" style="font-size: 16px;">arrow_forward</span>
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach

                {{-- Productos con Descuento Destacados --}}
                @foreach ($carruselOfertas as $cOferta)
                    @if($cOferta->tienda)
                        <a href="/app/tienda/{{ $cOferta->tienda->slug }}" class="stitch-carousel-card" style="border-left: 3px solid var(--pk-secondary-container);">
                            <div class="stitch-carousel-cover">
                                @if ($cOferta->fotos->isNotEmpty())
                                    <img src="{{ $cOferta->fotos->first()->url }}" alt="{{ $cOferta->nombre }}" loading="lazy">
                                @else
                                    <div class="stitch-product-placeholder">🍪</div>
                                @endif
                                <div class="scrim"></div>

                                <div class="stitch-carousel-badge-top">
                                    <span class="stitch-badge-pill stitch-badge-descuento">
                                        <span>🔥 -{{ $cOferta->descuento_porcentaje ?: $cOferta->porcentaje_descuento }}% OFF</span>
                                    </span>
                                    <span class="stitch-badge-pill stitch-badge-abierto">
                                        <span>Disponible</span>
                                    </span>
                                </div>
                            </div>

                            <div class="stitch-carousel-content">
                                <div style="display: flex; align-items: center; justify-content: space-between; font-size: 12px; color: var(--pk-on-surface-variant);">
                                    <span style="font-weight: 600;">{{ $cOferta->tienda->nombre }}</span>
                                    @if ($cOferta->tienda->estrellasPromedio())
                                        <span style="color: #b45309; font-weight: 700;">★ {{ number_format($cOferta->tienda->estrellasPromedio(), 1) }}</span>
                                    @endif
                                </div>

                                <h3 class="stitch-store-name" style="font-size: 15px;">{{ $cOferta->nombre }}</h3>

                                <div class="stitch-product-prices">
                                    <span class="stitch-price-final">${{ number_format($cOferta->precio_efectivo, 2) }}</span>
                                    <span class="stitch-price-orig">${{ number_format($cOferta->precio, 2) }}</span>
                                </div>

                                <div class="stitch-card-footer">
                                    <span class="stitch-location-tag">
                                        <span class="material-symbols-outlined" style="font-size: 15px; color: var(--pk-primary);">pin_drop</span>
                                        <span>{{ $cOferta->tienda->puntoEntrega?->nombre ?? 'Campus UTB' }}</span>
                                    </span>
                                    <span class="stitch-cta-link">
                                        <span>Pedir ahora</span>
                                        <span class="material-symbols-outlined" style="font-size: 16px;">arrow_forward</span>
                                    </span>
                                </div>
                            </div>
                        </a>
                    @endif
                @endforeach
            </div>
        </section>
    @endif

    <!-- 6. CONTENEDOR DE PESTAÑA A: TIENDAS DE EMPRENDEDORES UTB -->
    <div id="view-stores-section" style="display: flex; flex-direction: column; gap: 1rem;">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <h3 style="font-size: 20px; font-weight: 700; color: var(--pk-on-surface); margin: 0;">
                Tiendas de Emprendedores UTB
            </h3>
            <span style="font-size: 13px; color: var(--pk-on-surface-variant);">
                @if ($busqueda)
                    Resultados para "{{ $busqueda }}" ({{ $tiendas->total() }})
                @else
                    Ordenado por actividad reciente
                @endif
            </span>
        </div>

        <div class="stitch-stores-grid">
            @forelse ($tiendas as $tienda)
                <article class="stitch-store-card">
                    <div>
                        <div class="stitch-store-header-row">
                            <div class="stitch-store-header-left">
                                <div class="stitch-store-avatar">
                                    @if ($tienda->logo_url)
                                        <img src="{{ $tienda->logo_url }}" alt="{{ $tienda->nombre }}"
                                             onerror="this.onerror=null;this.parentElement.innerHTML='{{ mb_substr($tienda->nombre, 0, 1) }}';">
                                    @else
                                        {{ mb_substr($tienda->nombre, 0, 1) }}
                                    @endif
                                </div>
                                <div class="stitch-store-details">
                                    <div class="stitch-title-badges">
                                        <h4 class="stitch-store-title">{{ $tienda->nombre }}</h4>
                                        @if ($tienda->plan === 'premium')
                                            <span class="stitch-badge-saco10" title="Plan Sacó 10">★ Sacó 10</span>
                                        @else
                                            <span class="stitch-badge-matriculado">Matriculado</span>
                                        @endif
                                    </div>
                                    <span class="stitch-category-text">
                                        🏷️ {{ $tienda->categoria1->nombre }}
                                        @if ($tienda->dias_atencion)
                                            • {{ $tienda->diasAtencionLegible() }}
                                        @endif
                                    </span>
                                </div>
                            </div>

                            <!-- Estado Abierto / Cerrado / Pausado -->
                            @if ($tienda->estaAbiertoAhora())
                                <span class="stitch-badge-pill stitch-badge-abierto">
                                    <span class="dot"></span>
                                    <span>Abierto</span>
                                </span>
                            @elseif (!$tienda->recibiendo_pedidos)
                                <span class="stitch-badge-pill stitch-badge-cerrado">
                                    <span>No recibe pedidos</span>
                                </span>
                            @else
                                <span class="stitch-badge-pill stitch-badge-cerrado" title="Horario: {{ $tienda->hora_apertura }}-{{ $tienda->hora_cierre }}">
                                    <span>Cerrado</span>
                                </span>
                            @endif
                        </div>

                        <!-- Metadatos de Calificación y Descuentos -->
                        <div class="stitch-store-meta-tags">
                            @if ($tienda->estrellasPromedio())
                                <span class="stitch-rating-badge">
                                    <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">star</span>
                                    <span>{{ number_format($tienda->estrellasPromedio(), 1) }}</span>
                                    <span style="font-size: 11px; opacity: 0.85;">({{ $tienda->estrellasTotal() }})</span>
                                </span>
                            @else
                                <span class="stitch-badge-matriculado">✨ Nueva</span>
                            @endif

                            @if ($tienda->productos->where('descuento_activo', true)->count() > 0)
                                <span class="stitch-badge-pill stitch-badge-descuento">🔥 Hasta -25% hoy</span>
                            @endif

                            @if ($tienda->estaAbiertoAhora())
                                <span style="font-size: 12px; color: var(--pk-green-text); font-weight: 600;">Responde de inmediato</span>
                            @endif
                        </div>

                        <p class="stitch-store-desc">
                            {{ $tienda->descripcion ?: 'Emprendimiento universitario verificado en el campus de la Universidad Técnica de Babahoyo.' }}
                        </p>
                    </div>

                    <div class="stitch-card-footer">
                        <span class="stitch-location-tag" title="Lugar de entrega">
                            <span class="material-symbols-outlined" style="font-size: 16px; color: var(--pk-primary);">pin_drop</span>
                            <span>{{ $tienda->puntoEntrega->nombre ?? 'Campus UTB' }}</span>
                            @if ($tienda->hora_apertura && $tienda->hora_cierre)
                                <span>• ⏰ {{ $tienda->hora_apertura }}-{{ $tienda->hora_cierre }}</span>
                            @endif
                        </span>

                        <a href="/app/tienda/{{ $tienda->slug }}" class="stitch-cta-link" style="text-decoration: none;">
                            <span>Visitar puesto</span>
                            <span class="material-symbols-outlined" style="font-size: 16px;">arrow_forward</span>
                        </a>
                    </div>
                </article>
            @empty
                <div class="stitch-empty-card" style="grid-column: 1 / -1;">
                    <div class="stitch-empty-icon">🏪</div>
                    <h3 class="stitch-empty-title">No encontramos tiendas con "{{ $busqueda }}"</h3>
                    <p class="stitch-empty-desc">
                        Intenta con otra palabra clave o selecciona otra categoría para explorar más emprendimientos universitarios activos.
                    </p>
                    <a href="/app" class="btn btn-primary" style="width: auto; height: 44px; padding: 0 24px; border-radius: 12px; text-decoration: none;">
                        Ver todas las tiendas
                    </a>
                </div>
            @endforelse
        </div>

        @if ($tiendas->hasPages())
            <div class="stitch-pagination-wrap">
                {{ $tiendas->withQueryString()->links() }}
            </div>
        @endif
    </div>

    <!-- 7. CONTENEDOR DE PESTAÑA B: PRODUCTOS POPULARES EN CAMPUS -->
    <div id="view-products-section" style="display: none; flex-direction: column; gap: 1rem;">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <h3 style="font-size: 20px; font-weight: 700; color: var(--pk-on-surface); margin: 0;">
                Productos Populares en Campus
            </h3>
            <span style="font-size: 13px; color: var(--pk-primary); font-weight: 600;">
                Precios accesibles para estudiantes
            </span>
        </div>

        <div class="stitch-products-grid">
            @forelse ($productos as $prod)
                <article class="stitch-product-card">
                    <div class="stitch-product-img-box">
                        @if ($prod->fotos->isNotEmpty())
                            <img src="{{ $prod->fotos->first()->url }}" alt="{{ $prod->nombre }}" loading="lazy">
                        @else
                            <div class="stitch-product-placeholder">📦</div>
                        @endif

                        <div class="stitch-product-badges">
                            @if ($prod->tiene_descuento)
                                <span class="stitch-badge-pill stitch-badge-descuento">
                                    -{{ $prod->descuento_porcentaje ?: $prod->porcentaje_descuento }}%
                                </span>
                            @endif
                            @if ($prod->disponible)
                                <span class="stitch-badge-pill stitch-badge-abierto">Disponible</span>
                            @else
                                <span class="stitch-badge-pill stitch-badge-cerrado">Agotado</span>
                            @endif
                        </div>
                    </div>

                    <div class="stitch-product-body">
                        <div>
                            <div class="stitch-prod-store-row">
                                <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 70%;">
                                    {{ $prod->tienda->nombre }}
                                </span>
                                @if ($prod->tienda->estrellasPromedio())
                                    <span style="color: #b45309; font-weight: 700;">★ {{ number_format($prod->tienda->estrellasPromedio(), 1) }}</span>
                                @endif
                            </div>

                            <h4 class="stitch-product-name" title="{{ $prod->nombre }}">
                                {{ $prod->nombre }}
                            </h4>

                            <div class="stitch-product-prices">
                                <span class="stitch-price-final">${{ number_format($prod->precio_efectivo, 2) }}</span>
                                @if ($prod->tiene_descuento)
                                    <span class="stitch-price-orig">${{ number_format($prod->precio, 2) }}</span>
                                @endif
                            </div>
                        </div>

                        <a href="/app/tienda/{{ $prod->tienda->slug }}" class="stitch-btn-order">
                            <span class="material-symbols-outlined" style="font-size: 18px;">shopping_cart</span>
                            <span>Pedir</span>
                        </a>
                    </div>
                </article>
            @empty
                <div class="stitch-empty-card" style="grid-column: 1 / -1;">
                    <div class="stitch-empty-icon">📦</div>
                    <h3 class="stitch-empty-title">Aún no hay productos en este criterio</h3>
                    <p class="stitch-empty-desc">
                        Los emprendedores actualizan sus vitrinas a diario. Prueba con otra categoría o palabra clave.
                    </p>
                    <a href="/app" class="btn btn-primary" style="width: auto; height: 44px; padding: 0 24px; border-radius: 12px; text-decoration: none;">
                        Explorar catálogo
                    </a>
                </div>
            @endforelse
        </div>

        @if ($productos->hasPages())
            <div class="stitch-pagination-wrap">
                {{ $productos->withQueryString()->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

@section('scripts')
<script>
    // Desplazar carrusel de destacadas
    function desplazarStitchCarrusel(dir) {
        const track = document.getElementById('stitchCarouselTrack');
        if (track) {
            track.scrollBy({ left: dir * 350, behavior: 'smooth' });
        }
    }

    // Alternar entre pestañas "Tiendas" y "Productos"
    function switchVitrinaTab(tab) {
        const storesSection = document.getElementById('view-stores-section');
        const productsSection = document.getElementById('view-products-section');
        const btnStores = document.getElementById('tab-btn-stores');
        const btnProducts = document.getElementById('tab-btn-products');
        const counterLabel = document.getElementById('tab-counter-label');

        if (tab === 'stores') {
            storesSection.style.display = 'flex';
            productsSection.style.display = 'none';
            btnStores.classList.add('active');
            btnProducts.classList.remove('active');
            if (counterLabel) {
                counterLabel.innerHTML = '<span class="material-symbols-outlined">verified</span><span><strong>{{ $tiendas->total() }} tiendas</strong> en campus UTB</span>';
            }
            history.replaceState(null, '', updateQueryParam('tab', 'stores'));
        } else {
            storesSection.style.display = 'none';
            productsSection.style.display = 'flex';
            btnStores.classList.remove('active');
            btnProducts.classList.add('active');
            if (counterLabel) {
                counterLabel.innerHTML = '<span class="material-symbols-outlined">inventory_2</span><span><strong>{{ $productos->total() }} productos</strong> disponibles</span>';
            }
            history.replaceState(null, '', updateQueryParam('tab', 'products'));
        }
    }

    function updateQueryParam(key, value) {
        const url = new URL(window.location.href);
        url.searchParams.set(key, value);
        return url.pathname + url.search;
    }

    // Si viene ?tab=products o ?p_page en la URL, activar la pestaña de productos
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('tab') === 'products' || urlParams.has('p_page')) {
            switchVitrinaTab('products');
        }
    });
</script>
@endsection
