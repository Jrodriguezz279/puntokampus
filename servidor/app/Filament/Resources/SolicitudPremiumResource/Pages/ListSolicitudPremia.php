<?php

namespace App\Filament\Resources\SolicitudPremiumResource\Pages;

use App\Filament\Resources\SolicitudPremiumResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSolicitudPremia extends ListRecords
{
    protected static string $resource = SolicitudPremiumResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
