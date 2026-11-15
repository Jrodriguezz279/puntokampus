<?php

namespace App\Http\Middleware;

use Filament\Http\Middleware\Authenticate as FilamentAuthenticate;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class FilamentAdminAuthenticate extends FilamentAuthenticate
{
    /**
     * @param  array<string>  ...$guards
     */
    protected function authenticate($request, array $guards): void
    {
        $guard = count($guards) ? $this->auth->guard($guards[0]) : $this->auth->guard();

        if (! $guard->check()) {
            $this->unauthenticated($request, $guards);
            return;
        }

        $this->auth->shouldUse($guards[0] ?? null);

        $user = $guard->user();

        $panel = filament()->getCurrentPanel();

        if ($user instanceof FilamentUser) {
            if (! $user->canAccessPanel($panel)) {
                // Si el usuario actual (ej. estudiante) no es admin, cerrar su sesión
                // y redirigir limpiamente a la pantalla de login del panel
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $this->unauthenticated($request, $guards);
                return;
            }

            return;
        }

        abort(403);
    }
}
