<?php

namespace App\Console\Commands;

use App\Models\Configuracion;
use App\Models\SolicitudPremium;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PurgarComprobantesCommand extends Command
{
    protected $signature = 'app:purgar-comprobantes';

    protected $description = 'Elimina archivos físicos de comprobantes resueltos con más de 30 días';

    public function handle(): int
    {
        $config = Configuracion::first();
        $dias = (int) ($config->dias_borrado_comprobante ?? 30);
        $limite = now()->subDays($dias);

        $solicitudes = SolicitudPremium::whereIn('estado', ['aprobada', 'rechazada'])
            ->whereNotNull('revisado_en')
            ->where('revisado_en', '<', $limite)
            ->whereNull('foto_borrada_en')
            ->get();

        $contador = 0;
        foreach ($solicitudes as $solicitud) {
            $rawFoto = $solicitud->getRawOriginal('foto_comprobante');
            if ($rawFoto && Storage::disk('local')->exists($rawFoto)) {
                Storage::disk('local')->delete($rawFoto);
            }
            $solicitud->update(['foto_borrada_en' => now()]);
            $contador++;
        }

        $this->info("Comprobantes purgados: {$contador}");

        return Command::SUCCESS;
    }
}

