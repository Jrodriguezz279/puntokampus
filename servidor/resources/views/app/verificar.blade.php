@extends('app.layout')

@section('titulo', 'Verificar Código — PuntoKampus')

@section('content')

    <div style="max-width: 450px; margin: 2rem auto;">
        <div class="card" style="text-align: center; padding: 2.25rem 1.75rem;">
            <div style="font-size: 3rem; margin-bottom: 0.5rem;">📩</div>
            <h1 style="font-size: 1.35rem; font-weight: 800; color: var(--primary); margin:0;">Revisa tu correo</h1>
            <p style="font-size: 0.9rem; color: var(--text-muted); margin: 0.5rem 0 1.5rem;">
                Enviamos un código de 6 dígitos a:<br>
                <strong style="color: var(--text);">{{ $correo }}</strong>
            </p>

            @if ($codigoDemo)
                <div style="background: #fef3c7; border: 1px solid #fde68a; border-radius: 8px; padding: 0.75rem; margin-bottom: 1.5rem; font-size: 0.85rem; color: #92400e;">
                    💡 <strong>Código generado en entorno local:</strong> <code style="font-size: 1.2rem; font-weight: 800; letter-spacing: 2px;">{{ $codigoDemo }}</code>
                </div>
            @endif

            <form method="POST" action="/app/login/verificar">
                @csrf
                <div style="margin-bottom: 1.5rem;">
                    <label for="codigo" style="display:block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem;">
                        Código de 6 dígitos
                    </label>
                    <input type="text" id="codigo" name="codigo" maxlength="6" inputmode="numeric" required
                           class="form-control" style="width: 200px; margin: 0 auto; text-align: center; font-size: 1.8rem; font-weight: 800; letter-spacing: 6px; padding: 0.5rem; border: 2px solid var(--primary); border-radius: 10px;"
                           placeholder="000000" autofocus value="{{ $codigoDemo ?? '' }}">
                </div>

                <button type="submit" class="btn btn-primary" style="font-size: 1rem; min-height:46px;">
                    Verificar y Entrar 🚀
                </button>
            </form>

            <p style="margin-top: 1.5rem; font-size: 0.85rem; color: var(--text-muted);">
                ¿No te llegó? Revisa en tu carpeta de correo no deseado o
                <a href="/app/login" style="color: var(--primary); font-weight: 600;">cambiar de correo</a>.
            </p>
        </div>
    </div>

@endsection
