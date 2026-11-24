<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PedidoResource\Pages;
use App\Models\Pedido;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PedidoResource extends Resource
{
    protected static ?string $model = Pedido::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationLabel = 'Pedidos';

    protected static ?string $pluralModelLabel = 'Pedidos';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Información del Pedido')->schema([
                Forms\Components\TextInput::make('codigo')->label('Código')->disabled(),
                Forms\Components\TextInput::make('tienda.nombre')->label('Tienda')->disabled(),
                Forms\Components\TextInput::make('comprador.nombres')->label('Comprador')->disabled(),
                Forms\Components\TextInput::make('total')->label('Total')->prefix('$')->disabled(),
                Forms\Components\TextInput::make('estado')->label('Estado')->disabled(),
                Forms\Components\DateTimePicker::make('hora_entrega')->label('Hora de Entrega')->disabled(),
            ])->columns(2),

            Forms\Components\Section::make('Calificaciones')->schema([
                Forms\Components\Placeholder::make('calificacion_tienda')
                    ->label('Calificación a la Tienda (por el comprador)')
                    ->content(function (?Pedido $record): string {
                        if (!$record) return '—';
                        $cal = $record->calificacionATienda;
                        if (!$cal) return 'Sin calificar';
                        return str_repeat('⭐', $cal->estrellas) . " ({$cal->estrellas}/5) — " . ($cal->creada_en ? $cal->creada_en->format('d/m/Y H:i') : '');
                    }),

                Forms\Components\Placeholder::make('calificacion_comprador')
                    ->label('Calificación al Comprador (por el vendedor)')
                    ->content(function (?Pedido $record): string {
                        if (!$record) return '—';
                        $cal = $record->calificacionAComprador;
                        if (!$cal) return 'Sin calificar';
                        return str_repeat('⭐', $cal->estrellas) . " ({$cal->estrellas}/5) — " . ($cal->creada_en ? $cal->creada_en->format('d/m/Y H:i') : '');
                    }),

                Forms\Components\Placeholder::make('estado_visibilidad')
                    ->label('Estado de Visibilidad para las partes')
                    ->content(function (?Pedido $record): string {
                        if (!$record) return '—';
                        if ($record->calificacionesSonVisibles()) {
                            return '✅ Visibles (ambas partes calificaron o pasaron 3 días)';
                        }
                        return '🔒 Ocultas (esperando calificación mutua o transcurso de 3 días)';
                    }),
            ])->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('creado_en', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('codigo')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tienda.nombre')
                    ->label('Tienda')
                    ->searchable(),
                Tables\Columns\TextColumn::make('comprador.nombres')
                    ->label('Comprador')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('forma_pago')
                    ->badge(),
                Tables\Columns\TextColumn::make('estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pendiente' => 'warning',
                        'entregado' => 'success',
                        'cancelado' => 'danger',
                        'vencido'   => 'gray',
                        default     => 'gray',
                    }),
                Tables\Columns\TextColumn::make('hora_entrega')
                    ->dateTime(),
                Tables\Columns\IconColumn::make('codigo_bloqueado')
                    ->label('Bloqueado')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-lock-open')
                    ->trueColor('danger')
                    ->falseColor('success'),
                Tables\Columns\TextColumn::make('creado_en')
                    ->label('Fecha')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('desbloquear')
                    ->label('Desbloquear')
                    ->icon('heroicon-o-lock-open')
                    ->color('warning')
                    ->visible(fn (Pedido $record): bool => (bool) $record->codigo_bloqueado)
                    ->requiresConfirmation()
                    ->modalHeading('Desbloquear código de entrega')
                    ->modalDescription('Esto reiniciará los intentos fallidos a 0 y desbloqueará el pedido.')
                    ->action(function (Pedido $record) {
                        $record->update([
                            'codigo_bloqueado' => false,
                            'intentos_codigo'  => 0,
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
            'index' => Pages\ListPedidos::route('/'),
        ];
    }
}
