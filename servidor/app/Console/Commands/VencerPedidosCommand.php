<?php

namespace App\Console\Commands;

use App\Models\Pedido;
use Illuminate\Console\Command;

class VencerPedidosCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:vencer-pedidos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vence pedidos en estado pendiente cuya hora de entrega superó las 24 horas';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $limite = now()->subHours(24);

        $afectados = Pedido::where('estado', 'pendiente')
            ->where('hora_entrega', '<', $limite)
            ->update([
                'estado'             => 'vencido',
                'cancelado_por'      => 'sistema',
                'estado_cambiado_en' => now(),
            ]);

        $this->info("Pedidos vencidos procesados: {$afectados}");

        return Command::SUCCESS;
    }
}

