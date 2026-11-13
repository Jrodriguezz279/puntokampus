<?php

namespace App\Filament\Resources\SolicitudPremiumResource\Pages;

use App\Filament\Resources\SolicitudPremiumResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSolicitudPremium extends EditRecord
{
    protected static string $resource = SolicitudPremiumResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
