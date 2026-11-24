<?php

namespace App\Filament\Pages\Auth;

use App\Models\Usuario;
use App\Services\AuthService;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    /**
     * Extrae las credenciales del formulario mapeando 'email' a la columna 'correo' de la BD.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'correo'   => $data['email'],
            'password' => $data['password'],
        ];
    }

    /**
     * @return array<int | string, string | Form>
     */
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent(),
                        TextInput::make('codigo')
                            ->label('Código 2FA (enviado a tu correo)')
                            ->placeholder('Código de 6 dígitos')
                            ->maxLength(6)
                            ->autocomplete('one-time-code'),
                        $this->getRememberFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();
            return null;
        }

        $data = $this->form->getState();
        $correo = strtolower(trim($data['email'] ?? ''));
        $password = $data['password'] ?? '';
        $codigo = trim($data['codigo'] ?? '');

        // 1. Validar que el usuario exista, tenga contraseña correcta y sea admin activo
        $usuario = Usuario::where('correo', $correo)->first();
        if (!$usuario || !$usuario->es_admin || $usuario->estado !== 'activa' || !Hash::check($password, $usuario->password)) {
            $this->throwFailureValidationException();
        }

        $authService = app(AuthService::class);

        // 2. Si no ha ingresado el código, enviamos el código con propósito 'panel' y pedimos que lo ingrese
        if (empty($codigo)) {
            try {
                $authService->solicitarCodigo($correo, 'panel');
            } catch (\Throwable $e) {
                throw ValidationException::withMessages([
                    'data.codigo' => [$e->getMessage()],
                ]);
            }

            throw ValidationException::withMessages([
                'data.codigo' => ['Hemos enviado un código de 6 dígitos a tu correo. Ingrésalo para entrar al panel.'],
            ]);
        }

        // 3. Si ingresó código, verificarlo con propósito 'panel'
        try {
            $authService->verificarCodigo($correo, $codigo, proposito: 'panel');
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'data.codigo' => [$e->getMessage()],
            ]);
        }

        // 4. Iniciar sesión en Filament
        if (! Filament::auth()->attempt($this->getCredentialsFromFormData($data), $data['remember'] ?? false)) {
            $this->throwFailureValidationException();
        }

        session()->regenerate();

        return app(LoginResponse::class);
    }
}
