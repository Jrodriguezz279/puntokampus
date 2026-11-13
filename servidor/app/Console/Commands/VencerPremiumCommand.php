<?php

namespace App\Console\Commands;

use App\Models\Tienda;
use Illuminate\Console\Command;

class VencerPremiumCommand extends Command
{
    protected $signature = 'app:vencer-premium';

    protected $description = 'Pasa a plan gratis las tiendas cuya suscripción Premium haya vencido';

    public function handle(): int
    {
        $hoy = now()->toDateString();

        $afectadas = Tienda::where('plan', 'premium')
            ->whereNotNull('premium_hasta')
            ->where('premium_hasta', '<', $hoy)
            ->update([
                'plan' => 'gratis',
            ]);

        $this->info("Tiendas devueltas a plan gratis: {$afectadas}");

        return Command::SUCCESS;
    }
}

