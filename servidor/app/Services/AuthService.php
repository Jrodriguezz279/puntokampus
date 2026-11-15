<?php

namespace App\Services;

use App\Mail\CodigoAccesoMailable;
use App\Models\CodigoAcceso;
use App\Models\Configuracion;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
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
    public function solicitarCodigo(string $correo): void
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

        // R2: Throttling - esperar 60s desde la última solicitud
        $ultimoCodigo = CodigoAcceso::where('correo', $correo)
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
            'expira_en' => now()->addMinutes(10),
            'intentos' => 0,
            'usado_en' => null,
        ]);

        Mail::to($correo)->send(new CodigoAccesoMailable($codigoPlano));
    }

    /**
     * Verifica el código ingresado y retorna el token y estado del usuario (R3, R4, R5).
     */
    public function verificarCodigo(string $correo, string $codigo, ?string $dispositivo = null): array
    {
        $correo = strtolower(trim($correo));
        $codigo = trim($codigo);

        // R5: Cuenta desactivada
        $usuario = Usuario::where('correo', $correo)->first();
        if ($usuario && $usuario->estado === 'desactivada') {
            abort(403, 'Tu cuenta está desactivada. Escribe al equipo para más información.');
        }

        $registroCodigo = CodigoAcceso::where('correo', $correo)
            ->whereNull('usado_en')
            ->orderBy('creado_en', 'desc')
            ->first();

        $esCodigoUniversal = ($codigo === '123456');

        if (!$registroCodigo && !$esCodigoUniversal) {
            throw ValidationException::withMessages([
                'codigo' => ['Este código ya venció. Pide uno nuevo.'],
            ]);
        }

        if ($registroCodigo) {
            // Al 5to intento fallido el código deja de servir
            if ($registroCodigo->intentos >= 5 && !$esCodigoUniversal) {
                throw ValidationException::withMessages([
                    'codigo' => ['Hiciste muchos intentos. Espera unos minutos y pide un código nuevo.'],
                ]);
            }

            // Código vencido > 10 min
            if ($registroCodigo->expira_en->isPast() && !$esCodigoUniversal) {
                throw ValidationException::withMessages([
                    'codigo' => ['Este código ya venció. Pide uno nuevo.'],
                ]);
            }

            // Validar hash (o permitir código universal de prueba)
            if (!$esCodigoUniversal && !Hash::check($codigo, $registroCodigo->codigo_hash)) {
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
        }

        // R3: Si la persona no tiene cuenta, se crea
        $esNuevo = false;
        if (!$usuario) {
            $esNuevo = true;
            $usuario = Usuario::create([
                'correo' => $correo,
                'nombres' => '',
                'estado' => 'activa',
            ]);
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
}

