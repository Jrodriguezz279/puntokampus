<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Mail\CodigoBorradoMailable;
use App\Models\CodigoAcceso;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\ProductoFoto;
use App\Models\SolicitudPremium;
use App\Models\Tienda;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PaginasPublicasController extends Controller
{
    /**
     * GET /privacidad — Política de privacidad pública.
     */
    public function privacidad(): View
    {
        return view('paginas.privacidad');
    }

    /**
     * GET /borrar-cuenta — Formulario de borrado de cuenta (Paso 1: ingresar correo).
     */
    public function borrarCuentaForm(): View
    {
        return view('paginas.borrar_cuenta');
    }

    /**
     * POST /borrar-cuenta/codigo — Envía código de confirmación por correo.
     */
    public function enviarCodigoWeb(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'correo' => ['required', 'email', 'regex:/^[a-z0-9._%+-]+@([a-z0-9.-]+\.)?utb\.edu\.ec$/i'],
        ], [
            'correo.required' => 'El correo es obligatorio.',
            'correo.email'    => 'Ingresa un correo válido.',
            'correo.regex'    => 'Solo se aceptan correos de la UTB (terminan en utb.edu.ec).',
        ]);

        $correo = strtolower(trim($datos['correo']));

        // Verificar que el usuario existe
        $usuario = Usuario::where('correo', $correo)->first();
        if (!$usuario) {
            return back()->withErrors(['correo' => 'No encontramos ninguna cuenta con ese correo.'])->withInput();
        }

        // Throttle: 60s entre solicitudes
        $ultimoCodigo = CodigoAcceso::where('correo', $correo)
            ->orderBy('creado_en', 'desc')
            ->first();

        if ($ultimoCodigo && $ultimoCodigo->creado_en && $ultimoCodigo->creado_en->gt(now()->subSeconds(60))) {
            $segundosRestantes = 60 - now()->diffInSeconds($ultimoCodigo->creado_en);
            return back()->withErrors(['correo' => "Por favor espera {$segundosRestantes} segundos antes de pedir otro código."])->withInput();
        }

        // Generar código de 6 dígitos
        $codigoPlano = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        CodigoAcceso::create([
            'correo'      => $correo,
            'codigo_hash' => Hash::make($codigoPlano),
            'expira_en'   => now()->addMinutes(15),
            'intentos'    => 0,
            'usado_en'    => null,
        ]);

        Mail::to($correo)->send(new CodigoBorradoMailable($codigoPlano));

        return back()->with([
            'exito'  => true,
            'correo' => $correo,
        ]);
    }

    /**
     * POST /borrar-cuenta/confirmar — Verifica código y elimina la cuenta.
     */
    public function confirmarBorradoWeb(Request $request): View|RedirectResponse
    {
        $datos = $request->validate([
            'correo' => ['required', 'email'],
            'codigo' => ['required', 'string', 'size:6', 'regex:/^\d{6}$/'],
        ]);

        $correo = strtolower(trim($datos['correo']));
        $codigo = trim($datos['codigo']);

        $usuario = Usuario::where('correo', $correo)->first();
        if (!$usuario) {
            return back()->withErrors(['correo' => 'No encontramos ninguna cuenta con ese correo.'])->withInput();
        }

        $registroCodigo = CodigoAcceso::where('correo', $correo)
            ->whereNull('usado_en')
            ->orderBy('creado_en', 'desc')
            ->first();

        if (!$registroCodigo || $registroCodigo->expira_en->isPast()) {
            return back()->withErrors(['codigo' => 'El código expiró. Pide uno nuevo.'])->withInput();
        }

        if ($registroCodigo->intentos >= 5) {
            return back()->withErrors(['codigo' => 'Demasiados intentos fallidos. Pide un código nuevo.'])->withInput();
        }

        if (!Hash::check($codigo, $registroCodigo->codigo_hash)) {
            $registroCodigo->increment('intentos');
            $restantes = 5 - $registroCodigo->intentos;
            return back()->withErrors(['codigo' => "Código incorrecto. Te quedan {$restantes} intentos."])->withInput();
        }

        // Código válido — proceder con borrado
        $registroCodigo->update(['usado_en' => now()]);

        $this->anonimizarUsuario($usuario);

        return view('paginas.borrar_cuenta', ['eliminado' => true]);
    }

    /**
     * Convierte una URL pública de Storage en una ruta relativa para Storage::disk('public').
     * Ej: "/storage/logos/abc.jpg" → "logos/abc.jpg"
     */
    private function urlARutaPublica(?string $url): ?string
    {
        if (!$url) {
            return null;
        }
        // Quitar el prefijo /storage/ que Laravel agrega
        $prefijo = '/storage/';
        if (str_starts_with($url, $prefijo)) {
            return substr($url, strlen($prefijo));
        }
        return null;
    }

    /**
     * Anonimiza al usuario: cancela pedidos, elimina tienda y datos personales (R29, R30, R31).
     */
    private function anonimizarUsuario(Usuario $usuario): void
    {
        // R30: Si tiene tienda, cancelar pedidos pendientes y limpiar recursos
        $tienda = Tienda::where('usuario_id', $usuario->id)->first();
        if ($tienda) {
            // Cancelar pedidos pendientes de la tienda
            Pedido::where('tienda_id', $tienda->id)
                ->where('estado', 'pendiente')
                ->update([
                    'estado'        => 'cancelado',
                    'cancelado_por' => 'sistema',
                ]);

            // Eliminar fotos de productos del disco
            $productos = Producto::where('tienda_id', $tienda->id)->get();
            foreach ($productos as $producto) {
                $fotos = ProductoFoto::where('producto_id', $producto->id)->get();
                foreach ($fotos as $foto) {
                    $ruta = $this->urlARutaPublica($foto->url);
                    if ($ruta && Storage::disk('public')->exists($ruta)) {
                        Storage::disk('public')->delete($ruta);
                    }
                    $foto->delete();
                }
                $producto->delete();
            }

            // Eliminar comprobantes de solicitudes premium del disco
            $solicitudes = SolicitudPremium::where('tienda_id', $tienda->id)->get();
            foreach ($solicitudes as $solicitud) {
                if ($solicitud->foto_comprobante && Storage::disk('local')->exists($solicitud->foto_comprobante)) {
                    Storage::disk('local')->delete($solicitud->foto_comprobante);
                }
                $solicitud->delete();
            }

            // Eliminar logo y portada de la tienda del disco
            $rutaLogo = $this->urlARutaPublica($tienda->logo_url);
            if ($rutaLogo && Storage::disk('public')->exists($rutaLogo)) {
                Storage::disk('public')->delete($rutaLogo);
            }
            $rutaPortada = $this->urlARutaPublica($tienda->portada_url);
            if ($rutaPortada && Storage::disk('public')->exists($rutaPortada)) {
                Storage::disk('public')->delete($rutaPortada);
            }

            // Si existen pedidos asociados a la tienda, no se puede hacer DELETE físico por la FK pedidos.tienda_id.
            // Se da de baja y se anonimiza. Si no hay pedidos, se elimina físicamente.
            if (Pedido::where('tienda_id', $tienda->id)->exists()) {
                $tienda->update([
                    'estado'          => 'dada_de_baja',
                    'motivo_baja'     => 'Cuenta de usuario eliminada',
                    'dada_de_baja_en' => now(),
                    'nombre'          => 'Tienda eliminada',
                    'descripcion'     => null,
                    'logo_url'        => null,
                    'portada_url'     => null,
                ]);
            } else {
                $tienda->delete();
            }
        }

        // R31: Anonimizar datos personales del usuario
        $usuario->update([
            'nombres'           => 'Usuario eliminado',
            'correo'            => "eliminado_{$usuario->id}@borrado.utb",
            'whatsapp'          => null,
            'codigo_estudiante' => null,
            'facultad'          => null,
            'carrera'           => null,
            'semestre'          => null,
        ]);

        // Revocar todos los tokens activos
        $usuario->tokens()->delete();
    }
}
