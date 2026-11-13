<?php

namespace App\Filament\Resources\PuntoEntregaResource\Pages;

use App\Filament\Resources\PuntoEntregaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPuntoEntregas extends ListRecords
{
    protected static string $resource = PuntoEntregaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
