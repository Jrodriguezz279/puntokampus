<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('titulo', 'PuntoKampus — Marketplace UTB')</title>
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
            --radius: 16px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.5;
            -webkit-tap-highlight-color: transparent;
            padding-bottom: 75px; /* Espacio para barra inferior */
        }

        /* Top App Bar */
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
        }

        .top-brand {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-weight: 800;
            font-size: 1.2rem;
            color: var(--primary);
            text-decoration: none;
        }

        .top-brand span.campus {
            color: var(--accent);
        }

        .user-pill {
            font-size: 0.8rem;
            background: var(--primary-light);
            color: var(--primary);
            padding: 0.25rem 0.65rem;
            border-radius: 20px;
            font-weight: 600;
            text-decoration: none;
        }

        /* Contenedor principal */
        .main-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 1rem;
        }

        /* Bottom Navigation Bar (4 secciones oficiales del PRD) */
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
        }

        .nav-item.active {
            color: var(--primary);
        }

        .nav-item .icon {
            font-size: 1.25rem;
        }

        /* Componentes Comunes */
        .card {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 1.25rem;
            margin-bottom: 1rem;
            border: 1px solid var(--border);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 48px;
            padding: 0.6rem 1.25rem;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            border: none;
            gap: 0.4rem;
            transition: all 0.2s;
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

        .badge {
            display: inline-block;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 0.2rem 0.55rem;
            border-radius: 6px;
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
    </style>
    @yield('styles')
</head>
<body>

    <!-- Barra Superior -->
    <header class="top-bar">
        <a href="/app" class="top-brand">
            🏪 Punto<span class="campus">Kampus</span>
        </a>
        @auth
            <a href="/app/cuenta" class="user-pill">
                👤 {{ Str::limit(Auth::user()->nombres ?: Auth::user()->correo, 15) }}
            </a>
        @else
            <a href="/app/login" class="user-pill" style="background:var(--accent); color:white;">
                Entrar
            </a>
        @endauth
    </header>

    <!-- Contenido -->
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

    <!-- Barra Inferior Oficial del PRD (Vitrina, Mis pedidos, Mi tienda, Cuenta) -->
    <nav class="bottom-nav">
        <a href="/app" class="nav-item {{ request()->is('app') || request()->is('app/tienda*') ? 'active' : '' }}">
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
