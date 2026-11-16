<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo', 'PuntoKampus — Marketplace UTB')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #CC4E01;
            --primary-dark: #A83F00;
            --primary-light: #F7E3D6;
            --accent: #8A3500;
            --bg: #FAF5F2;
            --surface: #FEFAF8;
            --text: #171717;
            --text-muted: #77716D;
            --border: #D9D2CC;
            --radius: 12px;
            --radius-sm: 8px;
            --shadow: 0 1px 3px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.07), 0 2px 4px rgba(0,0,0,0.06);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.5;
            -webkit-tap-highlight-color: transparent;
        }

        /* =========================================================
           TOP BAR — mobile default, transforms to full nav on desktop
           ========================================================= */
        .top-bar {
            position: sticky;
            top: 0;
            z-index: 40;
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 0.75rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: var(--shadow);
        }

        .top-brand {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 800;
            font-size: 1.2rem;
            color: var(--primary);
            text-decoration: none;
            white-space: nowrap;
        }

        .top-brand span.campus { color: var(--accent); }

        /* Desktop nav links — hidden on mobile */
        .desktop-nav {
            display: none;
        }

        .desktop-nav a {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-muted);
            text-decoration: none;
            padding: 0.4rem 0.75rem;
            border-radius: var(--radius-sm);
            transition: color 0.15s, background 0.15s;
        }

        .desktop-nav a:hover,
        .desktop-nav a.active {
            color: var(--primary);
            background: var(--primary-light);
        }

        .user-pill {
            font-size: 0.8rem;
            background: var(--primary-light);
            color: var(--primary);
            padding: 0.3rem 0.75rem;
            border-radius: 20px;
            font-weight: 600;
            text-decoration: none;
            font-family: inherit;
            white-space: nowrap;
            transition: background 0.15s;
        }

        .user-pill:hover { background: #f0cbb5; }

        /* =========================================================
           MAIN CONTAINER
           ========================================================= */
        .main-container {
            max-width: 100%;
            margin: 0 auto;
            padding: 1rem;
        }

        /* =========================================================
           BOTTOM NAV — mobile only
           ========================================================= */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 50;
            background: var(--surface);
            border-top: 1px solid var(--border);
            display: flex;
            height: 64px;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.04);
        }

        body { padding-bottom: 75px; }

        .nav-item {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: var(--text-muted);
            font-size: 0.72rem;
            font-weight: 600;
            gap: 0.2rem;
            transition: color 0.15s;
            font-family: inherit;
        }

        .nav-item.active { color: var(--primary); }
        .nav-item .icon { font-size: 1.25rem; }

        /* =========================================================
           SHARED COMPONENTS
           ========================================================= */
        .card {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 1.25rem;
            margin-bottom: 1rem;
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            padding: 0.6rem 1.25rem;
            border-radius: var(--radius-sm);
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            border: none;
            gap: 0.4rem;
            transition: all 0.2s;
            font-family: inherit;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            width: 100%;
        }

        .btn-primary:hover { background: var(--primary-dark); }

        .btn-accent {
            background: var(--accent);
            color: white;
        }

        .btn-secondary {
            background: #f3f4f6;
            color: var(--text);
        }

        .btn-secondary:hover { background: #e5e7eb; }

        .badge {
            display: inline-block;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 0.2rem 0.55rem;
            border-radius: 6px;
            font-family: inherit;
        }

        .badge-matriculado { background: #e0e7ff; color: #4338ca; }
        .badge-saco10 { background: #fef3c7; color: #b45309; }
        .badge-pendiente { background: #fef3c7; color: #b45309; }
        .badge-entregado { background: #dcfce7; color: #15803d; }
        .badge-cancelado { background: #fee2e2; color: #b91c1c; }

        .alert-flash {
            background: #dcfce7;
            border-left: 4px solid #16a34a;
            color: #14532d;
            padding: 0.75rem 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .alert-error {
            background: #fee2e2;
            border-left: 4px solid #dc2626;
            color: #7f1d1d;
            padding: 0.75rem 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }

        /* =========================================================
           TABLET — ≥ 768px
           ========================================================= */
        @media (min-width: 768px) {
            .main-container {
                max-width: 900px;
                padding: 1.5rem 2rem;
            }
        }

        /* =========================================================
           DESKTOP — ≥ 1024px
           ========================================================= */
        @media (min-width: 1024px) {
            /* Show horizontal desktop nav, hide bottom nav */
            .bottom-nav { display: none; }
            body { padding-bottom: 0; }

            /* Header layout */
            .top-bar {
                padding: 0 2rem;
                height: 60px;
                gap: 1.5rem;
            }

            .top-brand { font-size: 1.3rem; }

            .desktop-nav {
                display: flex;
                align-items: center;
                gap: 0.25rem;
                flex: 1;
                justify-content: center;
            }

            .desktop-nav a {
                font-size: 0.92rem;
                padding: 0.45rem 0.9rem;
            }

            .user-pill {
                font-size: 0.85rem;
                padding: 0.35rem 0.9rem;
            }

            /* Main container */
            .main-container {
                max-width: 1200px;
                padding: 2rem 2.5rem;
            }
        }
    </style>
    @yield('styles')
</head>
<body>

    <!-- Barra Superior / Header -->
    <header class="top-bar">
        <a href="/app" class="top-brand">
            🏪 Punto<span class="campus">Kampus</span>
        </a>

        <!-- Desktop Navigation Links -->
        <nav class="desktop-nav">
            <a href="/app" class="{{ request()->is('app') || (request()->is('app/tienda*') && !request()->is('app/mi-tienda*')) ? 'active' : '' }}">
                🛍️ Vitrina
            </a>
            @auth
                <a href="/app/mis-pedidos" class="{{ request()->is('app/mis-pedidos*') ? 'active' : '' }}">
                    📦 Mis Pedidos
                </a>
                <a href="/app/mi-tienda" class="{{ request()->is('app/mi-tienda*') ? 'active' : '' }}">
                    🏪 Mi Tienda
                </a>
            @else
                <a href="/app/login">📦 Mis Pedidos</a>
                <a href="/app/login">🏪 Mi Tienda</a>
            @endauth
        </nav>

        <!-- User Pill -->
        @auth
            <a href="/app/cuenta" class="user-pill">
                👤 {{ Str::limit(Auth::user()->nombres ?: Auth::user()->correo, 18) }}
            </a>
        @else
            <a href="/app/login" class="user-pill" style="background:var(--accent); color:white;">
                Entrar
            </a>
        @endauth
    </header>

    <!-- Contenido Principal -->
    <main class="main-container">
        @if (session('mensaje'))
            <div class="alert-flash">{{ session('mensaje') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert-error">
                {{ $errors->first() }}
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Barra Inferior — solo visible en móvil (oculta ≥1024px via CSS) -->
    <nav class="bottom-nav">
        <a href="/app" class="nav-item {{ request()->is('app') || (request()->is('app/tienda*') && !request()->is('app/mi-tienda*')) ? 'active' : '' }}">
            <span class="icon">🛍️</span>
            <span>Vitrina</span>
        </a>

        @auth
            <a href="/app/mis-pedidos" class="nav-item {{ request()->is('app/mis-pedidos*') ? 'active' : '' }}">
                <span class="icon">📦</span>
                <span>Mis pedidos</span>
            </a>

            <a href="/app/mi-tienda" class="nav-item {{ request()->is('app/mi-tienda*') ? 'active' : '' }}">
                <span class="icon">🏪</span>
                <span>Mi tienda</span>
            </a>

            <a href="/app/cuenta" class="nav-item {{ request()->is('app/cuenta*') ? 'active' : '' }}">
                <span class="icon">👤</span>
                <span>Cuenta</span>
            </a>
        @else
            <a href="/app/login" class="nav-item">
                <span class="icon">📦</span>
                <span>Mis pedidos</span>
            </a>

            <a href="/app/login" class="nav-item">
                <span class="icon">🏪</span>
                <span>Mi tienda</span>
            </a>

            <a href="/app/login" class="nav-item {{ request()->is('app/login*') ? 'active' : '' }}">
                <span class="icon">👤</span>
                <span>Entrar</span>
            </a>
        @endauth
    </nav>

    @yield('scripts')
</body>
</html>
