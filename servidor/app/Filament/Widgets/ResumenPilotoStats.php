<?php

namespace App\Filament\Widgets;

use App\Models\Pedido;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ResumenPilotoStats extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    public static function obtenerResumenPiloto(): array
    {
        $confirmadasCodigo = Pedido::where('estado', 'entregado')
            ->where('entregado_con', 'codigo')
            ->count();

        $entregadasAdmin = Pedido::where('estado', 'entregado')
            ->where('entregado_con', 'administrador')
            ->count();

        $entregadasBoton = Pedido::where('estado', 'entregado')
            ->where('entregado_con', 'boton')
            ->count();

        $vencidos = Pedido::where('estado', 'vencido')->count();

        $canceladosAdmin = Pedido::where('estado', 'cancelado')
            ->where('cancelado_por', 'administrador')
            ->count();

        return [
            'confirmadas_con_codigo' => $confirmadasCodigo,
            'entregadas_administrador' => $entregadasAdmin,
            'entregadas_boton' => $entregadasBoton,
            'vencidos' => $vencidos,
            'cancelados_administrador' => $canceladosAdmin,
        ];
    }

    protected function getStats(): array
    {
        $resumen = static::obtenerResumenPiloto();

        return [
            Stat::make('Confirmadas con Código (QR/PIN)', $resumen['confirmadas_con_codigo'])
                ->description('Entregas validadas con código único')
                ->color('success')
                ->icon('heroicon-o-qr-code'),

            Stat::make('Entregadas por Administrador', $resumen['entregadas_administrador'])
                ->description('Corregidas manualmente tras vencer')
                ->color('warning')
                ->icon('heroicon-o-shield-check'),

            Stat::make('Confirmadas con Botón', $resumen['entregadas_boton'])
                ->description('Entregas directas sin código')
                ->color('gray')
                ->icon('heroicon-o-check'),

            Stat::make('Pedidos Vencidos', $resumen['vencidos'])
                ->description('Sin confirmación tras 24h')
                ->color('danger')
                ->icon('heroicon-o-clock'),
        ];
    }
}
