@extends('app.layout')

@section('titulo', 'Mi Cuenta — PuntoKampus')

@section('content')

    <div style="max-width: 540px; margin: 1rem auto;">
        <div class="card" style="text-align: center; padding: 2rem 1.5rem; margin-bottom: 1.25rem;">
            <div style="width: 72px; height: 72px; border-radius: 50%; background: var(--primary-light); color: var(--primary); font-size: 2rem; font-weight: 800; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.75rem;">
                {{ mb_substr($usuario->nombres ?: $usuario->correo, 0, 1) }}
            </div>
            <h1 style="font-size: 1.3rem; font-weight: 800; margin:0;">{{ $usuario->nombres ?: 'Estudiante UTB' }}</h1>
            <p style="font-size: 0.88rem; color: var(--text-muted); margin-top:0.25rem;">{{ $usuario->correo }}</p>
            @if ($usuario->facultad)
                <span class="badge" style="background:#f3f4f6; color:var(--text); margin-top:0.5rem; font-size:0.8rem; padding:0.3rem 0.7rem;">
                    🎓 {{ $usuario->carrera }} (Semestre {{ $usuario->semestre }})
                </span>
            @endif
        </div>

        <div class="card" style="padding: 0; overflow: hidden; margin-bottom: 1.25rem;">
            <a href="/privacidad" style="display:flex; justify-content:space-between; align-items:center; padding:1.1rem 1.25rem; text-decoration:none; color:inherit; border-bottom:1px solid var(--border); transition: background 0.15s;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='transparent'">
                <span style="font-weight:500;">🛡️ Política de Privacidad</span>
                <span style="color:var(--text-muted);">›</span>
            </a>
            <a href="/borrar-cuenta" style="display:flex; justify-content:space-between; align-items:center; padding:1.1rem 1.25rem; text-decoration:none; color:#dc2626; border-bottom:1px solid var(--border); transition: background 0.15s;" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='transparent'">
                <span style="font-weight:500;">🗑️ Eliminar mi cuenta</span>
                <span style="color:var(--text-muted);">›</span>
            </a>
        </div>

        <!-- Cambiar de Usuario Rápido (para evaluadores) -->
        <div class="card" style="background:#f0ebff; border-color:#d8b4fe; margin-bottom: 1.25rem;">
            <strong style="color:var(--primary); font-size:0.88rem; display:block; margin-bottom:0.4rem;">
                ⚡ Probar otro rol con 1 clic:
            </strong>
            <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
                <a href="/app/login-rapido/vendedor" class="btn" style="flex:1; background:var(--primary); color:white; min-height:38px; font-size:0.82rem; padding:0.4rem 0.75rem;">
                    Modo Vendedor (Carlos)
                </a>
                <a href="/app/login-rapido/comprador" class="btn" style="flex:1; background:var(--accent); color:white; min-height:38px; font-size:0.82rem; padding:0.4rem 0.75rem;">
                    Modo Comprador (María)
                </a>
            </div>
        </div>

        <form method="POST" action="/app/cuenta/salir">
            @csrf
            <button type="submit" class="btn btn-secondary" style="width:100%; color:#dc2626; border:1px solid var(--border); min-height:44px;">
                Cerrar Sesión 🚪
            </button>
        </form>
    </div>

@endsection
