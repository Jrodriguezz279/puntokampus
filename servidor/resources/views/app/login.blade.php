@extends('app.layout')

@section('titulo', 'Iniciar Sesión — PuntoKampus UTB')

@section('content')

    <div style="max-width: 460px; margin: 2rem auto;">
        <div class="card" style="padding: 2.25rem 2rem; border-radius: 16px;">
            <div style="text-align: center; margin-bottom: 1.5rem;">
                <div style="font-size: 2.8rem; margin-bottom: 0.25rem;">🎓</div>
                <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--primary); margin: 0;">Iniciar Sesión</h1>
                <p style="font-size: 0.88rem; color: var(--text-muted); margin: 0.35rem 0 0;">
                    Comunidad universitaria de la Universidad Técnica de Babahoyo.
                </p>
            </div>

            @if(session('mensaje'))
                <div style="background: #e6f4ea; color: #137333; padding: 0.85rem 1rem; border-radius: 8px; font-size: 0.88rem; margin-bottom: 1.25rem; font-weight: 500;">
                    ✓ {{ session('mensaje') }}
                </div>
            @endif

            @if($errors->any())
                <div style="background: #fce8e6; color: #c5221f; padding: 0.85rem 1rem; border-radius: 8px; font-size: 0.88rem; margin-bottom: 1.25rem;">
                    @foreach($errors->all() as $err)
                        <div>• {{ $err }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="/app/login/entrar">
                @csrf
                <div style="text-align: left; margin-bottom: 1.15rem;">
                    <label for="correo" style="display:block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.35rem;">
                        Correo Institucional UTB
                    </label>
                    <input type="email" id="correo" name="correo" placeholder="ejemplo@est.utb.edu.ec" required
                           autocomplete="username"
                           class="form-control" style="width:100%; padding:0.75rem 1rem; border:1.5px solid var(--border); border-radius:10px; font-size:0.95rem;"
                           value="{{ old('correo') }}" autofocus>
                </div>

                <div style="text-align: left; margin-bottom: 1rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                        <label for="contrasena" style="font-size: 0.85rem; font-weight: 600; margin:0;">
                            Contraseña
                        </label>
                        <a href="/app/olvide-contrasena" style="font-size: 0.8rem; color: var(--primary); text-decoration: none; font-weight: 600;">
                            ¿Olvidaste tu contraseña?
                        </a>
                    </div>
                    <div style="position: relative;">
                        <input type="password" id="contrasena" name="contrasena" placeholder="••••••••" required
                               autocomplete="current-password"
                               class="form-control" style="width:100%; padding:0.75rem 2.5rem 0.75rem 1rem; border:1.5px solid var(--border); border-radius:10px; font-size:0.95rem;">
                        <button type="button" onclick="togglePass('contrasena')" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--text-muted); font-size: 1.1rem;">
                            👁️
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%; font-size: 1rem; min-height:46px; border-radius:10px; font-weight:700;">
                    Iniciar Sesión →
                </button>
            </form>

            <div style="margin-top: 1.5rem; text-align: center; font-size: 0.9rem; color: var(--text-muted);">
                ¿Aún no tienes cuenta? 
                <a href="/app/registro" style="color: var(--primary); font-weight: 700; text-decoration: none;">
                    Crear cuenta con código
                </a>
            </div>

            <hr style="margin: 1.75rem 0 1.25rem; border: none; border-top: 1px solid var(--border);">

            <!-- Acceso Rápido de Prueba para Evaluadores -->
            <div style="background: var(--surface-container-low, #fbf2ed); border-radius: 12px; padding: 1.15rem; text-align: left; border: 1px solid var(--outline-variant, #e1c0b3);">
                <strong style="color: var(--primary); font-size: 0.88rem; display: block; margin-bottom: 0.3rem;">
                    ⚡ Cuentas de Prueba Guardadas:
                </strong>
                <p style="font-size: 0.8rem; color: var(--on-surface-variant, #594238); margin-bottom: 0.65rem;">
                    Puedes ingresar directamente con 1 clic o digitando sus credenciales:
                </p>
                <div style="display: flex; gap: 0.5rem; flex-direction: column;">
                    <a href="/app/login-rapido/vendedor" class="btn" style="background: var(--primary); color: white; min-height: 38px; font-size: 0.82rem; border-radius: 8px;">
                        🏪 Carlos Vendedor &nbsp;<code>Vendedor2026!</code>
                    </a>
                    <a href="/app/login-rapido/comprador" class="btn" style="background: var(--secondary, #6a5c52); color: white; min-height: 38px; font-size: 0.82rem; border-radius: 8px;">
                        🛍️ María Compradora &nbsp;<code>Compradora2026!</code>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePass(id) {
            const input = document.getElementById(id);
            if (input.type === 'password') {
                input.type = 'text';
            } else {
                input.type = 'password';
            }
        }
    </script>

@endsection
