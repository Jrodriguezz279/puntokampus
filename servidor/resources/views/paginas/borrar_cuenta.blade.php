<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminar mi cuenta — PuntoKampus</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'DM Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; color: #171717; background: #FAF5F2; line-height: 1.6; min-height: 100vh; display: flex; flex-direction: column; }
        header { background: #CC4E01; color: white; padding: 2rem 1rem; text-align: center; }
        header h1 { font-size: 1.6rem; font-weight: 700; }
        header p { opacity: 0.9; margin-top: 0.3rem; font-size: 0.9rem; }
        main { flex: 1; display: flex; align-items: flex-start; justify-content: center; padding: 2.5rem 1rem; }
        .card { background: #FEFAF8; border-radius: 16px; border: 1px solid #D9D2CC; padding: 2rem; max-width: 460px; width: 100%; box-shadow: 0 2px 12px rgba(23,23,23,.06); }
        .card-title { font-size: 1.15rem; font-weight: 700; margin-bottom: 1rem; color: #B71C1C; }
        .info-box { background: #F8DCDC; border: 1px solid #B71C1C; border-radius: 12px; padding: 1rem 1.1rem; margin-bottom: 1.5rem; font-size: 0.88rem; color: #B71C1C; }
        .info-box ul { margin: 0.4rem 0 0 1.1rem; }
        .info-box li { margin-bottom: 0.2rem; }
        label { display: block; font-size: 0.88rem; font-weight: 600; color: #77716D; margin-bottom: 0.4rem; }
        input[type="email"], input[type="text"] {
            width: 100%; padding: 0.65rem 0.9rem; border: 1px solid #D9D2CC; border-radius: 12px;
            font-size: 1rem; outline: none; background: #FEFAF8; transition: border-color .2s;
        }
        input:focus { border-color: #CC4E01; box-shadow: 0 0 0 2px rgba(204,78,1,.2); }
        .form-group { margin-bottom: 1.1rem; }
        .btn { width: 100%; padding: 0.8rem; border: none; border-radius: 12px; font-size: 1rem; font-weight: 700; cursor: pointer; transition: opacity .2s; }
        .btn-danger { background: #B71C1C; color: white; }
        .btn-danger:hover { opacity: 0.88; }
        .btn-secondary { background: #FEFAF8; border: 1px solid #D9D2CC; color: #171717; margin-top: 0.75rem; }
        .btn-secondary:hover { background: #ECE6E1; }
        .error-msg { color: #B71C1C; font-size: 0.83rem; margin-top: 0.3rem; }
        .success-box { background: #D8F0D5; border: 1px solid #176B27; border-radius: 12px; padding: 1rem 1.1rem; margin-bottom: 1.5rem; font-size: 0.9rem; color: #176B27; }
        .eliminated-box { text-align: center; padding: 1rem 0; }
        .eliminated-box .icon { font-size: 3rem; }
        .eliminated-box h2 { margin: 0.75rem 0 0.5rem; color: #171717; }
        .eliminated-box p { color: #77716D; font-size: 0.95rem; }
        footer { text-align: center; padding: 1.5rem; font-size: 0.82rem; color: #77716D; }
        a { color: #8A3500; font-weight: 600; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<header>
    <h1>🗑️ Eliminar mi cuenta</h1>
    <p>PuntoKampus — Universidad Técnica de Babahoyo</p>
</header>

<main>
    <div class="card">

        @if(isset($eliminado) && $eliminado)
        {{-- ✅ Cuenta eliminada exitosamente --}}
        <div class="eliminated-box">
            <div class="icon">✅</div>
            <h2>Cuenta eliminada</h2>
            <p>Tus datos personales han sido anonimizados y tu sesión cerrada en todos los dispositivos.</p>
            <p style="margin-top:0.75rem;">Gracias por haber usado PuntoKampus.</p>
        </div>

        @elseif(session('exito'))
        {{-- Paso 2: ingresar código --}}
        <p class="card-title">Paso 2 — Ingresa tu código de verificación</p>
        <div class="success-box">
            Enviamos un código de 6 dígitos a <strong>{{ session('correo') }}</strong>. Revisa tu correo institucional (incluida la carpeta de spam).
        </div>

        @if ($errors->any())
            <div class="info-box">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="/borrar-cuenta/confirmar">
            @csrf
            <input type="hidden" name="correo" value="{{ session('correo') }}">
            <div class="form-group">
                <label for="codigo">Código de verificación</label>
                <input type="text" id="codigo" name="codigo" maxlength="6" placeholder="000000"
                    autocomplete="one-time-code" inputmode="numeric" value="{{ old('codigo') }}" required>
                @error('codigo')<p class="error-msg">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="btn btn-danger">Confirmar y eliminar mi cuenta</button>
        </form>

        @else
        {{-- Paso 1: ingresar correo --}}
        <p class="card-title">Eliminar mi cuenta de PuntoKampus</p>

        <div class="info-box">
            <strong>⚠️ Esta acción es irreversible.</strong> Al eliminar tu cuenta:
            <ul>
                <li>Tus datos personales serán anonimizados.</li>
                <li>Tus pedidos pendientes como vendedor serán cancelados.</li>
                <li>Tus fotos y comprobantes serán eliminados.</li>
                <li>Tu sesión será cerrada en todos los dispositivos.</li>
            </ul>
        </div>

        @if ($errors->any())
            <div style="color:#c0392b; font-size:0.88rem; margin-bottom:1rem;">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="/borrar-cuenta/codigo">
            @csrf
            <div class="form-group">
                <label for="correo">Correo institucional UTB</label>
                <input type="email" id="correo" name="correo" placeholder="tucorreo@utb.edu.ec"
                    value="{{ old('correo') }}" required>
                @error('correo')<p class="error-msg">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="btn btn-danger">Enviar código de verificación</button>
        </form>

        <a href="/privacidad">
            <button type="button" class="btn btn-secondary">Ver política de privacidad</button>
        </a>

        @endif

    </div>
</main>

<footer>
    <p>© 2026 PuntoKampus — <a href="/privacidad">Política de privacidad</a></p>
</footer>

</body>
</html>

