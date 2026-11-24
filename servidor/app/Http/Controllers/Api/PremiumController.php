<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SolicitarPremiumRequest;
use App\Http\Resources\SolicitudPremiumResource;
use App\Models\Configuracion;
use App\Models\SolicitudPremium;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PremiumController extends Controller
{
    public function show(Request $request)
    {
        $tienda = $request->user()->tienda;

        if (!$tienda) {
            return response()->json([
                'message' => 'No tienes una tienda registrada.',
            ], 404);
        }

        $config = Configuracion::first();

        $ultimaSolicitud = SolicitudPremium::where('tienda_id', $tienda->id)
            ->latest('id')
            ->first();

        return response()->json([
            'tienda_plan'      => $tienda->plan,
            'premium_hasta'    => $tienda->premium_hasta?->toDateString(),
            'datos_pago'       => [
                'precio_premium' => (float) ($config->precio_premium ?? 5.00),
                'banco'          => $config->banco ?? '',
                'tipo_cuenta'    => $config->tipo_cuenta ?? '',
                'numero_cuenta'  => $config->numero_cuenta ?? '',
                'titular_cuenta' => $config->titular_cuenta ?? '',
                'usuario_deuna'  => $config->usuario_deuna ?? '',
            ],
            'ultima_solicitud' => $ultimaSolicitud ? new SolicitudPremiumResource($ultimaSolicitud) : null,
        ]);
    }

    public function store(SolicitarPremiumRequest $request)
    {
        $tienda = $request->user()->tienda;

        if (!$tienda) {
            return response()->json([
                'message' => 'No tienes una tienda registrada.',
            ], 404);
        }

        // R21: No permite otra solicitud mientras haya una pendiente
        $tienePendiente = SolicitudPremium::where('tienda_id', $tienda->id)
            ->where('estado', 'pendiente')
            ->exists();

        if ($tienePendiente) {
            return response()->json([
                'message' => 'Ya tienes una solicitud en revisión.',
            ], 422);
        }

        // Almacenamiento seguro y privado del comprobante
        $archivo = $request->file('foto_comprobante');
        $ruta = $archivo->store('comprobantes', 'local');

        $solicitud = SolicitudPremium::create([
            'tienda_id'          => $tienda->id,
            'numero_comprobante' => $request->input('numero_comprobante'),
            'monto'              => $request->input('monto'),
            'foto_comprobante'   => $ruta,
            'estado'             => 'pendiente',
        ]);

        return (new SolicitudPremiumResource($solicitud))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Ver foto de comprobante protegida (solo admin o la tienda dueña)
     */
    public function verFotoComprobante(Request $request, int|string $id)
    {
        $user = $request->user() ?? Auth::user();
        if (!$user) {
            abort(401, 'No autenticado.');
        }

        $solicitud = SolicitudPremium::findOrFail($id);

        $esDuenio = $solicitud->tienda && $solicitud->tienda->usuario_id === $user->id;
        $esAdmin = (bool) $user->es_admin;

        if (!$esAdmin && !$esDuenio) {
            abort(403, 'No autorizado para ver este comprobante.');
        }

        $rawPath = $solicitud->getRawOriginal('foto_comprobante');
        if (!$rawPath || !Storage::disk('local')->exists($rawPath)) {
            abort(404, 'Comprobante no encontrado.');
        }

        return response()->file(Storage::disk('local')->path($rawPath));
    }
}

