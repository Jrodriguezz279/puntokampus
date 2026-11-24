<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SolicitudPremiumResource\Pages;
use App\Models\SolicitudPremium;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;

class SolicitudPremiumResource extends Resource
{
    protected static ?string $model = SolicitudPremium::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationLabel = 'Solicitudes Premium';

    protected static ?string $pluralModelLabel = 'Solicitudes Premium';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('tienda_id')
                    ->relationship('tienda', 'nombre')
                    ->disabled(),
                Forms\Components\TextInput::make('numero_comprobante')
                    ->disabled(),
                Forms\Components\TextInput::make('monto')
                    ->prefix('$')
                    ->disabled(),
                Forms\Components\TextInput::make('estado')
                    ->disabled(),
                Forms\Components\Textarea::make('motivo_rechazo')
                    ->disabled()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('creado_en', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('tienda.nombre')
                    ->label('Tienda')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('numero_comprobante')
                    ->label('N° Comprobante')
                    ->searchable(),
                Tables\Columns\TextColumn::make('monto')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pendiente' => 'warning',
                        'aprobada'  => 'success',
                        'rechazada' => 'danger',
                        default     => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('revisado_en')
                    ->label('Revisado en')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('creado_en')
                    ->label('Fecha')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Action::make('verFoto')
                    ->label('Ver Comprobante')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn (SolicitudPremium $record): string => route('comprobantes.foto', $record->id))
                    ->openUrlInNewTab()
                    ->visible(fn (SolicitudPremium $record): bool => $record->foto_borrada_en === null),

                // Acción 1: Aprobar solicitud (H21, H24, R22)
                Action::make('aprobar')
                    ->label('Aprobar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (SolicitudPremium $record): bool => $record->estado === 'pendiente')
                    ->action(function (SolicitudPremium $record): void {
                        // R22: Verificar que el número de comprobante no haya sido usado en otra solicitud aprobada
                        $duplicado = SolicitudPremium::where('numero_comprobante', $record->numero_comprobante)
                            ->where('estado', 'aprobada')
                            ->where('id', '!=', $record->id)
                            ->exists();

                        if ($duplicado) {
                            Notification::make()
                                ->title('Error de duplicidad')
                                ->body("El número de comprobante {$record->numero_comprobante} ya fue aprobado en otra solicitud previa.")
                                ->danger()
                                ->send();
                            return;
                        }

                        $record->update([
                            'estado'       => 'aprobada',
                            'revisado_por' => auth()->id(),
                            'revisado_en'  => now(),
                        ]);

                        $tienda = $record->tienda;
                        if ($tienda) {
                            $tienda->update([
                                'plan'          => 'premium',
                                'premium_hasta' => Carbon::today()->addDays(30),
                            ]);
                        }

                        Notification::make()
                            ->title('Solicitud aprobada')
                            ->body("La tienda {$tienda?->nombre} ahora cuenta con Plan Premium.")
                            ->success()
                            ->send();
                    }),

                // Acción 2: Rechazar solicitud (H20, H24)
                Action::make('rechazar')
                    ->label('Rechazar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (SolicitudPremium $record): bool => $record->estado === 'pendiente')
                    ->form([
                        Forms\Components\Textarea::make('motivo_rechazo')
                            ->label('Motivo del rechazo (visible para el vendedor)')
                            ->required(),
                    ])
                    ->action(function (SolicitudPremium $record, array $data): void {
                        $record->update([
                            'estado'         => 'rechazada',
                            'motivo_rechazo' => $data['motivo_rechazo'],
                            'revisado_por'   => auth()->id(),
                            'revisado_en'    => now(),
                        ]);

                        Notification::make()
                            ->title('Solicitud rechazada')
                            ->body('Se ha registrado el rechazo y el motivo correspondiente.')
                            ->warning()
                            ->send();
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSolicitudPremia::route('/'),
        ];
    }
}
