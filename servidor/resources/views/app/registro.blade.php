@extends('app.layout')

@section('titulo', 'Crear Cuenta — PuntoKampus UTB')

@section('content')

    <div style="max-width: 460px; margin: 2rem auto;">
        <div class="card" style="padding: 2.25rem 2rem; border-radius: 16px;">
            <div style="text-align: center; margin-bottom: 1.5rem;">
                <div style="font-size: 2.8rem; margin-bottom: 0.25rem;">✨</div>
                <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--primary); margin: 0;">Crear Cuenta</h1>
                <p style="font-size: 0.88rem; color: var(--text-muted); margin: 0.35rem 0 0;">
                    Paso 1: Ingresa tu correo institucional de la UTB
                </p>
            </div>

            @if($errors->any())
                <div style="background: #fce8e6; color: #c5221f; padding: 0.85rem 1rem; border-radius: 8px; font-size: 0.88rem; margin-bottom: 1.25rem;">
                    @foreach($errors->all() as $err)
                        <div>• {{ $err }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="/app/registro/codigo">
                @csrf
                <div style="text-align: left; margin-bottom: 1.25rem;">
                    <label for="correo" style="display:block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.35rem;">
                        Correo Institucional UTB
                    </label>
                    <input type="email" id="correo" name="correo" placeholder="ejemplo@est.utb.edu.ec" required
                           autocomplete="email"
                           class="form-control" style="width:100%; padding:0.75rem 1rem; border:1.5px solid var(--border); border-radius:10px; font-size:0.95rem;"
                           value="{{ old('correo') }}" autofocus>
                    <span style="font-size: 0.78rem; color: var(--text-muted); margin-top: 0.4rem; display:block;">
                        Te enviaremos un código de 6 dígitos para validar tu identidad y crear tu contraseña.
                    </span>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%; font-size: 1rem; min-height:46px; border-radius:10px; font-weight:700;">
                    Enviar código de verificación ✉️
                </button>
            </form>

            <div style="margin-top: 1.5rem; text-align: center; font-size: 0.9rem; color: var(--text-muted);">
                ¿Ya tienes una cuenta registrada? 
                <a href="/app/login" style="color: var(--primary); font-weight: 700; text-decoration: none;">
                    Iniciar sesión
                </a>
            </div>
        </div>
    </div>

@endsection
