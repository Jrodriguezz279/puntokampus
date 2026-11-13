<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CrearReporteRequest;
use App\Models\Reporte;
use App\Models\Tienda;
use Carbon\Carbon;

class ReporteController extends Controller
{
    public function store(CrearReporteRequest $request, int $id)
    {
        $usuario = $request->user();
        $tienda = Tienda::find($id);

        if (!$tienda) {
            return response()->json([
                'message' => 'Tienda no encontrada.',
            ], 404);
        }

        // R20: Prohibido autoreporte
        if ($tienda->usuario_id === $usuario->id) {
            return response()->json([
                'message' => 'No puedes reportar tu propia tienda.',
            ], 422);
        }

        // R20: Límite de 1 reporte por tienda al día por usuario
        $yaReportoHoy = Reporte::where('tienda_id', $tienda->id)
            ->where('reportado_por', $usuario->id)
            ->whereDate('creado_en', Carbon::today())
            ->exists();

        if ($yaReportoHoy) {
            return response()->json([
                'message' => 'Ya reportaste esta tienda hoy.',
            ], 422);
        }

        Reporte::create([
            'tienda_id'     => $tienda->id,
            'reportado_por' => $usuario->id,
            'motivo'        => $request->input('motivo'),
            'detalle'       => $request->input('detalle'),
            'estado'        => 'nuevo',
        ]);

        return response()->json([
            'message' => 'Gracias. El equipo lo revisará en 24 horas',
        ], 201);
    }
}
