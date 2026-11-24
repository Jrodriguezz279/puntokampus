<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActualizarPerfilRequest;
use App\Http\Requests\ConfirmarBorradoRequest;
use App\Http\Resources\UsuarioResource;
use App\Mail\CodigoBorradoMailable;
use App\Models\CodigoAcceso;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\ProductoFoto;
use App\Models\SolicitudPremium;
use App\Models\Tienda;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class YoController extends Controller
{
    /**
     * GET /api/v1/yo
     */
    public function show(Request $request): JsonResponse
    {
        $usuario = $request->user()->load('tienda');

        $resumenTienda = null;
        if ($usuario->tienda) {
            $resumenTienda = [
                'id' => $usuario->tienda->id,
                'nombre' => $usuario->tienda->nombre,
                'slug' => $usuario->tienda->slug,
                'plan' => $usuario->tienda->plan,
                'estado' => $usuario->tienda->estado,
                'logo_url' => $usuario->tienda->logo_url,
            ];
        }

        return response()->json([
            'usuario' => new UsuarioResource($usuario),
            'tienda' => $resumenTienda,
        ]);
    }

    /**
     * PUT /api/v1/yo
     */
    public function update(ActualizarPerfilRequest $request): JsonResponse
    {
        $usuario = $request->user();
        $datos = $request->validated();

        if ($request->boolean('acepto_privacidad') && !$usuario->acepto_privacidad_en) {
            $datos['acepto_privacidad_en'] = now();
        }

        unset($datos['acepto_privacidad']);

        $usuario->update($datos);

        return response()->json([
            'usuario' => new UsuarioResource($usuario->fresh()),
        ]);
    }

    /**
     * POST /api/v1/yo/codigo-borrado (🔒)
     * Genera y envía un código de 6 dígitos para confirmar el borrado de cuenta (R29).
     */
    public function solicitarCodigoBorrado(Request $request): Response
    {
        $usuario = $request->user();
        $correo  = $usuario->correo;

        // Throttle: 60s entre solicitudes
        $ultimoCodigo = CodigoAcceso::where('correo', $correo)
            ->orderBy('creado_en', 'desc')
            ->first();

        if ($ultimoCodigo && $ultimoCodigo->creado_en && $ultimoCodigo->creado_en->gt(now()->subSeconds(60))) {
            $segundosRestantes = 60 - now()->diffInSeconds($ultimoCodigo->creado_en);
            abort(429, "Por favor espera {$segundosRestantes} segundos antes de pedir otro código.");
        }

        // Máximo 5 solicitudes por hora
        $solicitudesUltimaHora = CodigoAcceso::where('correo', $correo)
            ->where('creado_en', '>=', now()->subHour())
            ->count();

        if ($solicitudesUltimaHora >= 5) {
            abort(429, 'Hiciste muchos intentos. Espera unos minutos y pide un código nuevo.');
        }

        // Generar código de 6 dígitos
        $codigoPlano = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        CodigoAcceso::create([
            'correo'      => $correo,
            'codigo_hash' => Hash::make($codigoPlano),
            'proposito'   => 'borrado',
            'expira_en'   => now()->addMinutes(15),
            'intentos'    => 0,
            'usado_en'    => null,
        ]);

        Mail::to($correo)->send(new CodigoBorradoMailable($codigoPlano));

        return response()->noContent(); // 204
    }

    /**
     * DELETE /api/v1/yo (🔒)
     * Verifica el código y elimina/anonimiza la cuenta del usuario (R30, R31).
     */
    public function borrarCuenta(ConfirmarBorradoRequest $request): Response
    {
        $usuario = $request->user();
        $correo  = $usuario->correo;
        $codigo  = $request->input('codigo');

        // Verificar código
        $registroCodigo = CodigoAcceso::where('correo', $correo)
            ->where(function ($q) {
                $q->where('proposito', 'borrado')->orWhereNull('proposito');
            })
            ->whereNull('usado_en')
            ->orderBy('creado_en', 'desc')
            ->first();

        if (!$registroCodigo || $registroCodigo->expira_en->isPast()) {
            abort(422, 'El código expiró. Pide uno nuevo.');
        }

        if ($registroCodigo->intentos >= 5) {
            abort(422, 'Demasiados intentos fallidos. Pide un código nuevo.');
        }

        if (!Hash::check($codigo, $registroCodigo->codigo_hash)) {
            $registroCodigo->increment('intentos');
            $restantes = 5 - $registroCodigo->intentos;
            abort(422, "Código incorrecto. Te quedan {$restantes} intentos.");
        }

        // Marcar código como usado
        $registroCodigo->update(['usado_en' => now()]);

        // R30: Si tiene tienda, cancelar pedidos pendientes y limpiar recursos
        $tienda = Tienda::where('usuario_id', $usuario->id)->first();
        if ($tienda) {
            Pedido::where('tienda_id', $tienda->id)
                ->where('estado', 'pendiente')
                ->update([
                    'estado'        => 'cancelado',
                    'cancelado_por' => 'sistema',
                ]);

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

            $solicitudes = SolicitudPremium::where('tienda_id', $tienda->id)->get();
            foreach ($solicitudes as $solicitud) {
                if ($solicitud->foto_comprobante && Storage::disk('local')->exists($solicitud->foto_comprobante)) {
                    Storage::disk('local')->delete($solicitud->foto_comprobante);
                }
                $solicitud->delete();
            }

            $rutaLogo = $this->urlARutaPublica($tienda->logo_url);
            if ($rutaLogo && Storage::disk('public')->exists($rutaLogo)) {
                Storage::disk('public')->delete($rutaLogo);
            }
            $rutaPortada = $this->urlARutaPublica($tienda->portada_url);
            if ($rutaPortada && Storage::disk('public')->exists($rutaPortada)) {
                Storage::disk('public')->delete($rutaPortada);
            }
            $rutaDeuna = $this->urlARutaPublica($tienda->deuna_qr_url);
            if ($rutaDeuna && Storage::disk('public')->exists($rutaDeuna)) {
                Storage::disk('public')->delete($rutaDeuna);
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
                    'deuna_qr_url'    => null,
                ]);
            } else {
                $tienda->delete();
            }
        }

        // Fase 3: Calificaciones en borrado de cuenta
        // Las calificaciones que dio se conservan sin autor
        \App\Models\Calificacion::where('autor_id', $usuario->id)->update(['autor_id' => null]);

        // Las calificaciones que recibió se eliminan
        \App\Models\Calificacion::where('direccion', 'a_comprador')
            ->whereHas('pedido', fn($q) => $q->where('comprador_id', $usuario->id))
            ->delete();

        if ($tienda) {
            \App\Models\Calificacion::where('direccion', 'a_tienda')
                ->whereHas('pedido', fn($q) => $q->where('tienda_id', $tienda->id))
                ->delete();
        }

        // R31: Anonimizar datos personales
        $usuario->update([
            'nombres'           => 'Usuario eliminado',
            'correo'            => "eliminado_{$usuario->id}@borrado.utb",
            'whatsapp'          => null,
            'codigo_estudiante' => null,
            'facultad'          => null,
            'carrera'           => null,
            'semestre'          => null,
        ]);

        // Revocar todos los tokens
        $usuario->tokens()->delete();

        return response()->noContent(); // 204
    }

    /**
     * Convierte una URL pública de Storage en ruta relativa para Storage::disk('public').
     * Ej: "/storage/logos/abc.jpg" → "logos/abc.jpg"
     */
    private function urlARutaPublica(?string $url): ?string
    {
        if (!$url) {
            return null;
        }
        $prefijo = '/storage/';
        if (str_starts_with($url, $prefijo)) {
            return substr($url, strlen($prefijo));
        }
        return null;
    }
}

