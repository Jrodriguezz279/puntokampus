@extends('app.layout')

@section('titulo', 'Mi Cuenta — PuntoKampus')

@section('content')

    <div class="card" style="text-align: center; padding: 2rem 1.25rem; margin-bottom: 1.25rem;">
        <div style="width: 70px; height: 70px; border-radius: 50%; background: var(--primary-light); color: var(--primary); font-size: 2rem; font-weight: 800; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.75rem;">
            {{ mb_substr($usuario->nombres ?: $usuario->correo, 0, 1) }}
        </div>
        <h1 style="font-size: 1.3rem; font-weight: 800;">{{ $usuario->nombres ?: 'Estudiante UTB' }}</h1>
        <p style="font-size: 0.88rem; color: var(--text-muted);">{{ $usuario->correo }}</p>
        @if ($usuario->facultad)
            <span class="badge" style="background:#f3f4f6; color:var(--text); margin-top:0.4rem;">
                🎓 {{ $usuario->carrera }} (Semestre {{ $usuario->semestre }})
            </span>
        @endif
    </div>

    <div class="card" style="padding: 0; overflow: hidden; margin-bottom: 1.25rem;">
        <a href="/privacidad" style="display:flex; justify-content:space-between; align-items:center; padding:1rem 1.25rem; text-decoration:none; color:inherit; border-bottom:1px solid var(--border);">
            <span>🛡️ Política de Privacidad</span>
            <span style="color:var(--text-muted);">›</span>
        </a>
        <a href="/borrar-cuenta" style="display:flex; justify-content:space-between; align-items:center; padding:1rem 1.25rem; text-decoration:none; color:#dc2626; border-bottom:1px solid var(--border);">
            <span>🗑️ Eliminar mi cuenta</span>
            <span style="color:var(--text-muted);">›</span>
        </a>
    </div>

    <!-- Cambiar de Usuario Rápido (para evaluadores) -->
    <div class="card" style="background:#f0ebff; border-color:#d8b4fe; margin-bottom: 1.25rem;">
        <strong style="color:var(--primary); font-size:0.85rem; display:block; margin-bottom:0.4rem;">
            ⚡ Probar otro rol con 1 clic:
        </strong>
        <div style="display:flex; gap:0.5rem;">
            <a href="/app/login-rapido/vendedor" class="btn" style="flex:1; background:var(--primary); color:white; min-height:36px; font-size:0.8rem; padding:0.3rem;">
                Modo Vendedor (Carlos)
            </a>
            <a href="/app/login-rapido/comprador" class="btn" style="flex:1; background:var(--accent); color:white; min-height:36px; font-size:0.8rem; padding:0.3rem;">
                Modo Comprador (María)
            </a>
        </div>
    </div>

    <form method="POST" action="/app/cuenta/salir">
        @csrf
        <button type="submit" class="btn btn-secondary" style="width:100%; color:#dc2626; border:1px solid var(--border);">
            Cerrar Sesión 🚪
        </button>
    </form>

@endsection
