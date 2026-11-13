<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;

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
}
