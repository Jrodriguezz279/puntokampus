@extends('app.layout')

@section('titulo', 'Entrar — PuntoKampus UTB')

@section('content')

    <div class="card" style="text-align: center; padding: 2rem 1.5rem;">
        <div style="font-size: 3rem; margin-bottom: 0.5rem;">🎓</div>
        <h1 style="font-size: 1.4rem; font-weight: 800; color: var(--primary);">Entrar a PuntoKampus</h1>
        <p style="font-size: 0.9rem; color: var(--text-muted); margin: 0.5rem 0 1.5rem;">
            Solo para estudiantes y docentes con correo institucional de la Universidad Técnica de Babahoyo.
        </p>

        <form method="POST" action="/app/login/codigo">
            @csrf
            <div style="text-align: left; margin-bottom: 1.25rem;">
                <label for="correo" style="display:block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.4rem;">
                    Correo Institucional UTB
                </label>
                <input type="email" id="correo" name="correo" placeholder="ejemplo@est.utb.edu.ec" required
                       class="form-control" style="width:100%; padding:0.75rem 1rem; border:1.5px solid var(--border); border-radius:10px; font-size:1rem;"
                       value="{{ old('correo') }}" autofocus>
                <span style="font-size: 0.78rem; color: var(--text-muted); margin-top: 0.3rem; display:block;">
                    Te enviaremos un código de 6 dígitos (sin contraseñas).
                </span>
            </div>

            <button type="submit" class="btn btn-primary" style="font-size: 1rem;">
                Enviarme el código ✉️
            </button>
        </form>

        <hr style="margin: 2rem 0; border: none; border-top: 1px solid var(--border);">

        <!-- Acceso Rápido de Prueba para Evaluadores -->
        <div style="background: #f0ebff; border-radius: 10px; padding: 1.25rem; text-align: left;">
            <strong style="color: var(--primary); font-size: 0.88rem; display: block; margin-bottom: 0.5rem;">
                ⚡ Acceso Rápido para Pruebas:
            </strong>
            <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.75rem;">
                Entra con 1 clic para probar inmediatamente el rol de vendedor o compradora:
            </p>
            <div style="display: flex; gap: 0.5rem; flex-direction: column;">
                <a href="/app/login-rapido/vendedor" class="btn" style="background: var(--primary); color: white; min-height: 40px; font-size: 0.85rem;">
                    🏪 Entrar como Carlos Vendedor (Dulces & Snacks)
                </a>
                <a href="/app/login-rapido/comprador" class="btn" style="background: var(--accent); color: white; min-height: 40px; font-size: 0.85rem;">
                    🛍️ Entrar como María Compradora (Derecho)
                </a>
            </div>
        </div>
    </div>

@endsection
