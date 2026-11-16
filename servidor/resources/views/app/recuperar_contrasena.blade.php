@extends('app.layout')

@section('titulo', 'Nueva Contraseña — PuntoKampus UTB')

@section('content')

    <div style="max-width: 460px; margin: 2rem auto;">
        <div class="card" style="padding: 2.25rem 2rem; border-radius: 16px;">
            <div style="text-align: center; margin-bottom: 1.5rem;">
                <div style="font-size: 2.8rem; margin-bottom: 0.25rem;">✨</div>
                <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--primary); margin: 0;">Restablecer Contraseña</h1>
                <p style="font-size: 0.88rem; color: var(--text-muted); margin: 0.35rem 0 0;">
                    Enviamos un código a <strong>{{ $correo }}</strong>
                </p>
            </div>

            @if(isset($codigoDemo))
                <div style="background: #fbf2ed; border: 1px dashed var(--primary); border-radius: 8px; padding: 0.75rem 1rem; text-align: center; margin-bottom: 1.25rem; font-size: 0.85rem;">
                    Código de prueba rápido: <strong style="font-size: 1.15rem; color: var(--primary); letter-spacing: 2px;">{{ $codigoDemo }}</strong>
                </div>
            @endif

            @if($errors->any())
                <div style="background: #fce8e6; color: #c5221f; padding: 0.85rem 1rem; border-radius: 8px; font-size: 0.88rem; margin-bottom: 1.25rem;">
                    @foreach($errors->all() as $err)
                        <div>• {{ $err }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="/app/olvide-contrasena/recuperar">
                @csrf
                <div style="text-align: left; margin-bottom: 1.15rem;">
                    <label for="codigo" style="display:block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.35rem;">
                        Código de 6 dígitos
                    </label>
                    <input type="text" id="codigo" name="codigo" placeholder="123456" maxlength="6" required
                           style="width:100%; padding:0.75rem 1rem; border:1.5px solid var(--border); border-radius:10px; font-size:1.3rem; letter-spacing: 6px; text-align: center; font-weight: 700;"
                           value="{{ old('codigo', $codigoDemo ?? '') }}" autofocus>
                </div>

                <div style="text-align: left; margin-bottom: 1.35rem;">
                    <label for="contrasena" style="display:block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.35rem;">
                        Escribe tu Nueva Contraseña
                    </label>
                    <div style="position: relative;">
                        <input type="password" id="contrasena" name="contrasena" placeholder="Mínimo 6 caracteres" required minlength="6"
                               autocomplete="new-password"
                               class="form-control" style="width:100%; padding:0.75rem 2.5rem 0.75rem 1rem; border:1.5px solid var(--border); border-radius:10px; font-size:0.95rem;">
                        <button type="button" onclick="togglePass('contrasena')" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--text-muted); font-size: 1.1rem;">
                            👁️
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%; font-size: 1rem; min-height:46px; border-radius:10px; font-weight:700;">
                    Guardar nueva contraseña e Iniciar sesión →
                </button>
            </form>

            <div style="margin-top: 1.25rem; text-align: center;">
                <a href="/app/olvide-contrasena" style="font-size: 0.85rem; color: var(--text-muted); text-decoration: none;">
                    ← Reenviar código
                </a>
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
