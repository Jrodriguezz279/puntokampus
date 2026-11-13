<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReporteResource\Pages;
use App\Models\Pedido;
use App\Models\Reporte;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReporteResource extends Resource
{
    protected static ?string $model = Reporte::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationLabel = 'Reportes';

    protected static ?string $pluralModelLabel = 'Reportes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('tienda_id')
                    ->relationship('tienda', 'nombre')
                    ->required()
                    ->disabled(),
                Forms\Components\Select::make('reportado_por')
                    ->relationship('reportadoPor', 'nombres')
                    ->required()
                    ->disabled(),
                Forms\Components\TextInput::make('motivo')
                    ->disabled(),
                Forms\Components\Textarea::make('detalle')
                    ->disabled()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('estado')
                    ->disabled(),
                Forms\Components\Textarea::make('nota_admin')
                    ->label('Nota de Administración')
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
                Tables\Columns\TextColumn::make('reportadoPor.nombres')
                    ->label('Reportado por')
                    ->searchable(),
                Tables\Columns\TextColumn::make('motivo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'prohibido' => 'danger',
                        'no_entrego' => 'warning',
                        'informacion_falsa' => 'danger',
                        'trato_inadecuado' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('detalle')
                    ->limit(40)
                    ->tooltip(fn ($record): ?string => $record->detalle),
                Tables\Columns\TextColumn::make('estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'nuevo' => 'danger',
                        'sin_accion' => 'gray',
                        'tienda_dada_de_baja' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('revisadoPor.nombres')
                    ->label('Revisado por')
                    ->placeholder('Sin revisar'),
                Tables\Columns\TextColumn::make('creado_en')
                    ->label('Fecha')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                // Acción 1: Cerrar sin acción (H23)
                Action::make('cerrar_sin_accion')
                    ->label('Cerrar sin acción')
                    ->icon('heroicon-o-check')
                    ->color('gray')
                    ->visible(fn (Reporte $record): bool => $record->estado === 'nuevo')
                    ->form([
                        Forms\Components\Textarea::make('nota_admin')
                            ->label('Nota del Administrador (Justificación)')
                            ->required(),
                    ])
                    ->action(function (Reporte $record, array $data): void {
                        $record->update([
                            'estado'       => 'sin_accion',
                            'revisado_por' => auth()->id(),
                            'revisado_en'  => now(),
                            'nota_admin'   => $data['nota_admin'],
                        ]);
                    }),

                // Acción 2: Dar de baja tienda (H22, H23)
                Action::make('dar_de_baja_tienda')
                    ->label('Dar de baja tienda')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Reporte $record): bool => $record->estado === 'nuevo' && $record->tienda?->estado === 'activa')
                    ->form([
                        Forms\Components\Textarea::make('motivo_baja')
                            ->label('Motivo de la baja (obligatorio, visible para el vendedor)')
                            ->required(),
                        Forms\Components\Textarea::make('nota_admin')
                            ->label('Nota interna del administrador')
                            ->nullable(),
                    ])
                    ->action(function (Reporte $record, array $data): void {
                        $tienda = $record->tienda;

                        if ($tienda) {
                            $tienda->update([
                                'estado'          => 'dada_de_baja',
                                'motivo_baja'     => $data['motivo_baja'],
                                'dada_de_baja_en' => now(),
                            ]);

                            // Cancelar pedidos pendientes de la tienda con cancelado_por = sistema
                            Pedido::where('tienda_id', $tienda->id)
                                ->where('estado', 'pendiente')
                                ->update([
                                    'estado'             => 'cancelado',
                                    'cancelado_por'      => 'sistema',
                                    'estado_cambiado_en' => now(),
                                ]);
                        }

                        $record->update([
                            'estado'       => 'tienda_dada_de_baja',
                            'revisado_por' => auth()->id(),
                            'revisado_en'  => now(),
                            'nota_admin'   => $data['nota_admin'] ?? null,
                        ]);
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
            'index' => Pages\ListReportes::route('/'),
        ];
    }
}
