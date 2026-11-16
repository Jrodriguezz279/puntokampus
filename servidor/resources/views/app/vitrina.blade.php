@extends('app.layout')

@section('titulo', 'Vitrina — PuntoKampus UTB')

@section('styles')
<style>
    /* =============================================
       BUSCADOR
       ============================================= */
    .search-box {
        position: relative;
        margin-bottom: 1rem;
    }

    .search-input {
        width: 100%;
        padding: 0.8rem 1rem 0.8rem 2.6rem;
        border-radius: 25px;
        border: 1px solid var(--border);
        background: white;
        font-size: 0.95rem;
        font-family: inherit;
        outline: none;
        transition: border-color 0.15s, box-shadow 0.15s;
    }

    .search-input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px var(--primary-light);
    }

    .search-icon {
        position: absolute;
        left: 0.9rem;
        top: 50%;
        transform: translateY(-50%);
        font-size: 1rem;
        color: var(--text-muted);
        pointer-events: none;
    }

    /* =============================================
       CATEGORÍAS
       ============================================= */
    .categories-row {
        display: flex;
        gap: 0.5rem;
        overflow-x: auto;
        padding-bottom: 0.5rem;
        margin-bottom: 1.25rem;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }

    .categories-row::-webkit-scrollbar { display: none; }

    .category-chip {
        flex-shrink: 0;
        padding: 0.45rem 0.9rem;
        border-radius: 20px;
        background: white;
        border: 1px solid var(--border);
        font-size: 0.82rem;
        font-weight: 600;
        font-family: inherit;
        color: var(--text);
        text-decoration: none;
        transition: all 0.15s;
        white-space: nowrap;
    }

    .category-chip:hover { background: var(--primary-light); border-color: var(--primary); color: var(--primary); }
    .category-chip.active { background: var(--primary); color: white; border-color: var(--primary); }

    /* =============================================
       SECTION HEADER
       ============================================= */
    .section-title {
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 1rem;
        color: var(--text);
    }

    /* =============================================
       STORE CARDS — mobile: list, desktop: grid
       ============================================= */
    .stores-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .store-card {
        background: white;
        border-radius: var(--radius);
        padding: 1rem 1.1rem;
        border: 1px solid var(--border);
        display: flex;
        gap: 1rem;
        text-decoration: none;
        color: inherit;
        box-shadow: var(--shadow);
        transition: transform 0.15s, box-shadow 0.15s;
    }

    .store-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .store-avatar {
        width: 60px;
        height: 60px;
        border-radius: var(--radius-sm);
        background: var(--primary-light);
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        font-weight: 800;
        flex-shrink: 0;
        overflow: hidden;
    }

    .store-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .store-info { flex: 1; min-width: 0; }

    .store-header {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        margin-bottom: 0.25rem;
        flex-wrap: wrap;
    }

    .store-name {
        font-size: 1rem;
        font-weight: 700;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .store-desc {
        font-size: 0.82rem;
        color: var(--text-muted);
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        margin-bottom: 0.5rem;
    }

    .store-footer {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.78rem;
        color: var(--text-muted);
        flex-wrap: wrap;
    }

    /* =============================================
       EMPTY STATE
       ============================================= */
    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: var(--text-muted);
    }

    /* =============================================
       DESKTOP GRID — ≥ 768px
       ============================================= */
    @media (min-width: 768px) {
        .stores-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1rem;
        }

        .store-card {
            flex-direction: column;
            gap: 0.75rem;
            padding: 1.25rem;
            align-items: flex-start;
        }

        .store-avatar {
            width: 72px;
            height: 72px;
            font-size: 2rem;
        }

        .store-name { font-size: 1.05rem; white-space: normal; }

        .store-desc {
            -webkit-line-clamp: 3;
            font-size: 0.85rem;
        }
    }
</style>
@endsection

@section('content')

    <!-- Buscador -->
    <form method="GET" action="/app" class="search-box">
        <span class="search-icon">🔍</span>
        <input type="text" name="q" value="{{ $busqueda }}"
               placeholder="Buscar comida, dulces, impresiones..." class="search-input">
        @if ($categoriaId)
            <input type="hidden" name="categoria" value="{{ $categoriaId }}">
        @endif
    </form>

    <!-- Fila de Categorías Deslizables -->
    <div class="categories-row">
        <a href="/app{{ $busqueda ? '?q='.$busqueda : '' }}"
           class="category-chip {{ !$categoriaId ? 'active' : '' }}">
            Todas
        </a>
        @foreach ($categorias as $cat)
            <a href="/app?categoria={{ $cat->id }}{{ $busqueda ? '&q='.$busqueda : '' }}"
               class="category-chip {{ $categoriaId == $cat->id ? 'active' : '' }}">
                {{ $cat->nombre }}
            </a>
        @endforeach
    </div>

    <!-- Título de Sección -->
    <h2 class="section-title">
        @if ($busqueda)
            Resultados para "{{ $busqueda }}" ({{ $tiendas->total() }})
        @else
            Tiendas en el campus UTB ({{ $tiendas->total() }})
        @endif
    </h2>

    <!-- Lista / Grid de Tiendas -->
    <div class="stores-list">
        @forelse ($tiendas as $tienda)
            <a href="/app/tienda/{{ $tienda->slug }}" class="store-card">
                <div class="store-avatar">
                    @if ($tienda->logo_url)
                        <img src="{{ $tienda->logo_url }}" alt="{{ $tienda->nombre }}"
                             onerror="this.onerror=null;this.parentElement.innerHTML='{{ mb_substr($tienda->nombre, 0, 1) }}';">
                    @else
                        {{ mb_substr($tienda->nombre, 0, 1) }}
                    @endif
                </div>
                <div class="store-info">
                    <div class="store-header">
                        <span class="store-name">{{ $tienda->nombre }}</span>
                        @if ($tienda->plan === 'premium')
                            <span class="badge badge-saco10">⭐ Sacó 10</span>
                        @else
                            <span class="badge badge-matriculado">Matriculado</span>
                        @endif
                    </div>
                    <p class="store-desc">{{ $tienda->descripcion ?: 'Tienda universitaria en el campus UTB.' }}</p>
                    <div class="store-footer">
                        <span>📍 {{ $tienda->puntoEntrega->nombre ?? 'Campus UTB' }}</span>
                        <span>•</span>
                        <span>🏷️ {{ $tienda->categoria1->nombre }}</span>
                    </div>
                </div>
            </a>
        @empty
            <div class="empty-state">
                <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">🏪</div>
                <h3>No encontramos tiendas con ese criterio</h3>
                <p style="font-size: 0.9rem; margin-top: 0.3rem;">Prueba con otra palabra o categoría.</p>
            </div>
        @endforelse
    </div>

@endsection
