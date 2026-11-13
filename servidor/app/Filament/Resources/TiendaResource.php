<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TiendaResource\Pages;
use App\Models\Pedido;
use App\Models\Tienda;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;

class TiendaResource extends Resource
{
    protected static ?string $model = Tienda::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationLabel = 'Tiendas';

    protected static ?string $pluralModelLabel = 'Tiendas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->maxLength(40),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->disabled(),
                Forms\Components\Select::make('plan')
                    ->options([
                        'gratis'  => 'Gratis (Matriculado)',
                        'premium' => 'Premium (Sacó 10)',
                    ])
                    ->required(),
                Forms\Components\DatePicker::make('premium_hasta'),
                Forms\Components\Select::make('estado')
                    ->options([
                        'activa'       => 'Activa',
                        'dada_de_baja' => 'Dada de baja',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('motivo_baja')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('usuario.nombres')
                    ->label('Propietario')
                    ->searchable(),
                Tables\Columns\TextColumn::make('plan')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'premium' => 'warning',
                        default   => 'gray',
                    }),
                Tables\Columns\TextColumn::make('estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'activa'       => 'success',
                        'dada_de_baja' => 'danger',
                        default        => 'gray',
                    }),
                Tables\Columns\TextColumn::make('premium_hasta')
                    ->date()
                    ->placeholder('N/A'),
                Tables\Columns\TextColumn::make('creado_en')
                    ->label('Creada')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                // Acción 1: Dar de baja tienda (H22)
                Action::make('dar_de_baja')
                    ->label('Dar de baja')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Tienda $record): bool => $record->estado === 'activa')
                    ->form([
                        Forms\Components\Textarea::make('motivo_baja')
                            ->label('Motivo de la baja (obligatorio, visible para el vendedor)')
                            ->required(),
                    ])
                    ->action(function (Tienda $record, array $data): void {
                        $record->update([
                            'estado'          => 'dada_de_baja',
                            'motivo_baja'     => $data['motivo_baja'],
                            'dada_de_baja_en' => now(),
                        ]);

                        // Cancelar pedidos pendientes de la tienda
                        Pedido::where('tienda_id', $record->id)
                            ->where('estado', 'pendiente')
                            ->update([
                                'estado'             => 'cancelado',
                                'cancelado_por'      => 'sistema',
                                'estado_cambiado_en' => now(),
                            ]);

                        Notification::make()
                            ->title('Tienda dada de baja')
                            ->body('Se cancelaron sus pedidos pendientes y se notificó el motivo.')
                            ->danger()
                            ->send();
                    }),

                // Acción 2: Reactivar tienda (H22)
                Action::make('reactivar')
                    ->label('Reactivar')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Tienda $record): bool => $record->estado === 'dada_de_baja')
                    ->action(function (Tienda $record): void {
                        $record->update([
                            'estado'          => 'activa',
                            'motivo_baja'     => null,
                            'dada_de_baja_en' => null,
                        ]);

                        Notification::make()
                            ->title('Tienda reactivada')
                            ->body('La tienda ha vuelto a la vitrina pública.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTiendas::route('/'),
        ];
    }
}
