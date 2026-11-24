<?php

namespace App\Services;

use App\Mail\CodigoAccesoMailable;
use App\Models\CodigoAcceso;
use App\Models\Configuracion;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Valida si el correo pertenece al dominio utb.edu.ec o sus subdominios (R1).
     */
    public function esCorreoUtbValido(string $correo): bool
    {
        $correo = strtolower(trim($correo));
        return (bool) preg_match('/^[a-z0-9._%+-]+@([a-z0-9.-]+\.)?utb\.edu\.ec$/i', $correo);
    }

    /**
     * Solicita y envía un código de 6 dígitos con throttling (R1, R2, R5).
     */
    public function solicitarCodigo(string $correo, string $proposito = 'acceso'): void
    {
        $correo = strtolower(trim($correo));

        if (!$this->esCorreoUtbValido($correo)) {
            throw ValidationException::withMessages([
                'correo' => ['Solo se puede entrar con un correo de la UTB (termina en utb.edu.ec).'],
            ]);
        }

        // R5: Cuenta desactivada no puede ingresar
        $usuario = Usuario::where('correo', $correo)->first();
        if ($usuario && $usuario->estado === 'desactivada') {
            abort(403, 'Tu cuenta está desactivada. Escribe al equipo para más información.');
        }

        // R2: Throttling estricto (60s entre envíos y máximo 5 códigos por hora)
        $ultimoCodigo = CodigoAcceso::where('correo', $correo)
            ->where(function ($q) use ($proposito) {
                $q->where('proposito', $proposito)->orWhereNull('proposito');
            })
            ->orderBy('creado_en', 'desc')
            ->first();

        if ($ultimoCodigo && $ultimoCodigo->creado_en && $ultimoCodigo->creado_en->gt(now()->subSeconds(60))) {
            $segundosRestantes = 60 - now()->diffInSeconds($ultimoCodigo->creado_en);
            abort(429, "Por favor espera {$segundosRestantes} segundos antes de pedir otro código.");
        }

        // R2: Máximo 5 códigos por hora
        $solicitudesUltimaHora = CodigoAcceso::where('correo', $correo)
            ->where('creado_en', '>=', now()->subHour())
            ->count();

        if ($solicitudesUltimaHora >= 5) {
            abort(429, 'Hiciste muchos intentos. Espera unos minutos y pide un código nuevo.');
        }

        // Generar código de 6 dígitos aleatorio
        $codigoPlano = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        CodigoAcceso::create([
            'correo' => $correo,
            'codigo_hash' => Hash::make($codigoPlano),
            'proposito' => $proposito,
            'expira_en' => now()->addMinutes(10),
            'intentos' => 0,
            'usado_en' => null,
        ]);

        Mail::to($correo)->send(new CodigoAccesoMailable($codigoPlano));
    }

    /**
     * Verifica el código ingresado y retorna el token y estado del usuario (R3, R4, R5).
     */
    public function verificarCodigo(
        string $correo,
        string $codigo,
        ?string $dispositivo = null,
        ?string $password = null,
        string $proposito = 'acceso'
    ): array {
        $correo = strtolower(trim($correo));
        $codigo = trim($codigo);

        // R5: Cuenta desactivada
        $usuario = Usuario::where('correo', $correo)->first();
        if ($usuario && $usuario->estado === 'desactivada') {
            abort(403, 'Tu cuenta está desactivada. Escribe al equipo para más información.');
        }

        $registroCodigo = CodigoAcceso::where('correo', $correo)
            ->where(function ($q) use ($proposito) {
                if ($proposito === 'acceso') {
                    $q->whereIn('proposito', ['acceso', 'registro'])->orWhereNull('proposito');
                } else {
                    $q->where('proposito', $proposito);
                }
            })
            ->whereNull('usado_en')
            ->orderBy('creado_en', 'desc')
            ->first();

        if (!$registroCodigo) {
            throw ValidationException::withMessages([
                'codigo' => ['Este código ya venció. Pide uno nuevo.'],
            ]);
        }

        // Al 5to intento fallido el código deja de servir
        if ($registroCodigo->intentos >= 5) {
            throw ValidationException::withMessages([
                'codigo' => ['Hiciste muchos intentos. Espera unos minutos y pide un código nuevo.'],
            ]);
        }

        // Código vencido > 10 min
        if ($registroCodigo->expira_en->isPast()) {
            throw ValidationException::withMessages([
                'codigo' => ['Este código ya venció. Pide uno nuevo.'],
            ]);
        }

        // Validar hash
        if (!Hash::check($codigo, $registroCodigo->codigo_hash)) {
            $registroCodigo->increment('intentos');
            $restantes = 5 - $registroCodigo->intentos;

            if ($restantes <= 0) {
                throw ValidationException::withMessages([
                    'codigo' => ['Hiciste muchos intentos. Espera unos minutos y pide un código nuevo.'],
                ]);
            }

            throw ValidationException::withMessages([
                'codigo' => ["Ese código no es correcto. Te quedan {$restantes} intentos."],
            ]);
        }

        // Marcar como usado
        $registroCodigo->update(['usado_en' => now()]);

        // R3: Si la persona no tiene cuenta, se crea
        $esNuevo = false;
        if (!$usuario) {
            $esNuevo = true;
            $usuario = Usuario::create([
                'correo' => $correo,
                'nombres' => '',
                'password' => $password ? Hash::make($password) : null,
                'estado' => 'activa',
            ]);
        } elseif ($password) {
            $usuario->password = Hash::make($password);
            $usuario->save();
        }

        // R4: Token con vencimiento hasta fin de periodo académico (si está configurado)
        $config = Configuracion::first();
        $finPeriodo = $config?->fin_periodo_academico;
        $expiresAt = $finPeriodo ? now()->parse($finPeriodo)->endOfDay() : null;

        $tokenName = $dispositivo ? "auth_{$dispositivo}" : 'auth_token';
        $token = $usuario->createToken($tokenName, ['*'], $expiresAt)->plainTextToken;

        return [
            'token' => $token,
            'usuario' => $usuario,
            'es_nuevo' => $esNuevo,
        ];
    }

    /**
     * Iniciar sesión con correo y contraseña.
     */
    public function loginConContrasena(string $correo, string $password, ?string $dispositivo = null): array
    {
        $correo = strtolower(trim($correo));
        $throttleKey = 'login_contrasena:' . $correo;

        // R2/Seguridad: 5 intentos fallidos obligan a esperar
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $minutes = ceil($seconds / 60);
            throw ValidationException::withMessages([
                'contrasena' => ["Demasiados intentos fallidos. Por favor espera {$minutes} minuto(s) antes de volver a intentar."],
            ]);
        }

        $usuario = Usuario::where('correo', $correo)->first();

        if ($usuario && $usuario->estado === 'desactivada') {
            abort(403, 'Tu cuenta está desactivada. Escribe al equipo para más información.');
        }

        $passwordValida = false;
        if ($usuario && !empty($usuario->password)) {
            $passwordValida = Hash::check($password, $usuario->password);
        }

        if (!$usuario || !$passwordValida) {
            RateLimiter::hit($throttleKey, 600); // Bloqueo por 10 minutos
            throw ValidationException::withMessages([
                'contrasena' => ['El correo o la contraseña son incorrectos.'],
            ]);
        }

        RateLimiter::clear($throttleKey);

        $config = Configuracion::first();
        $finPeriodo = $config?->fin_periodo_academico;
        $expiresAt = $finPeriodo ? now()->parse($finPeriodo)->endOfDay() : null;

        $tokenName = $dispositivo ? "auth_{$dispositivo}" : 'auth_token';
        $token = $usuario->createToken($tokenName, ['*'], $expiresAt)->plainTextToken;

        return [
            'token' => $token,
            'usuario' => $usuario,
            'es_nuevo' => false,
        ];
    }

    /**
     * Solicita código de recuperación de contraseña.
     */
    public function solicitarCodigoRecuperacion(string $correo): void
    {
        $correo = strtolower(trim($correo));
        if (!$this->esCorreoUtbValido($correo)) {
            throw ValidationException::withMessages([
                'correo' => ['Solo se puede solicitar con un correo de la UTB (termina en utb.edu.ec).'],
            ]);
        }

        $usuario = Usuario::where('correo', $correo)->first();
        if ($usuario && $usuario->estado === 'activa') {
            $this->solicitarCodigo($correo, 'recuperacion');
        }
    }

    /**
     * Confirma código de recuperación y establece nueva contraseña.
     */
    public function recuperarContrasena(string $correo, string $codigo, string $nuevaPassword): void
    {
        $correo = strtolower(trim($correo));
        $codigo = trim($codigo);

        $usuario = Usuario::where('correo', $correo)->first();
        if (!$usuario || $usuario->estado === 'desactivada') {
            throw ValidationException::withMessages([
                'correo' => ['No pudimos procesar la recuperación para esta cuenta.'],
            ]);
        }

        $registroCodigo = CodigoAcceso::where('correo', $correo)
            ->where(function ($q) {
                $q->where('proposito', 'recuperacion')
                  ->orWhere('proposito', 'recuperar_contrasena');
            })
            ->whereNull('usado_en')
            ->orderBy('creado_en', 'desc')
            ->first();

        if (!$registroCodigo) {
            throw ValidationException::withMessages([
                'codigo' => ['Este código ya venció. Pide uno nuevo.'],
            ]);
        }

        if ($registroCodigo->intentos >= 5) {
            throw ValidationException::withMessages([
                'codigo' => ['Hiciste muchos intentos. Espera unos minutos y pide un código nuevo.'],
            ]);
        }
        if ($registroCodigo->expira_en->isPast()) {
            throw ValidationException::withMessages([
                'codigo' => ['Este código ya venció. Pide uno nuevo.'],
            ]);
        }
        if (!Hash::check($codigo, $registroCodigo->codigo_hash)) {
            $registroCodigo->increment('intentos');
            $restantes = 5 - $registroCodigo->intentos;
            if ($restantes <= 0) {
                throw ValidationException::withMessages([
                    'codigo' => ['Hiciste muchos intentos. Espera unos minutos y pide un código nuevo.'],
                ]);
            }
            throw ValidationException::withMessages([
                'codigo' => ["Ese código no es correcto. Te quedan {$restantes} intentos."],
            ]);
        }

        $registroCodigo->update(['usado_en' => now()]);

        $usuario->password = Hash::make($nuevaPassword);
        $usuario->save();

        // Revocar sesiones de token anteriores
        $usuario->tokens()->delete();
    }
}

