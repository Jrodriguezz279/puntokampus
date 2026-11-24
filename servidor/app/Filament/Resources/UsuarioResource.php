<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UsuarioResource\Pages;
use App\Models\Usuario;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UsuarioResource extends Resource
{
    protected static ?string $model = Usuario::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Usuarios';

    protected static ?string $pluralModelLabel = 'Usuarios';

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('correo')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nombres')
                    ->searchable(),
                Tables\Columns\TextColumn::make('facultad')
                    ->placeholder('N/A'),
                Tables\Columns\TextColumn::make('carrera')
                    ->placeholder('N/A'),
                Tables\Columns\TextColumn::make('semestre')
                    ->placeholder('N/A'),
                Tables\Columns\IconColumn::make('es_admin')
                    ->boolean(),
                Tables\Columns\TextColumn::make('estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'activa'      => 'success',
                        'desactivada' => 'danger',
                        default       => 'gray',
                    }),
                Tables\Columns\TextColumn::make('creado_en')
                    ->label('Registrado')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('suspender')
                    ->label('Suspender')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Usuario $record): bool => $record->estado === 'activa' && !$record->es_admin)
                    ->action(function (Usuario $record): void {
                        $record->update(['estado' => 'desactivada']);
                        $record->tokens()->delete();
                        \Filament\Notifications\Notification::make()
                            ->title('Usuario suspendido')
                            ->body("La cuenta de {$record->correo} ha sido suspendida y sus sesiones cerradas.")
                            ->danger()
                            ->send();
                    }),
                Tables\Actions\Action::make('activar')
                    ->label('Activar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Usuario $record): bool => $record->estado === 'desactivada')
                    ->action(function (Usuario $record): void {
                        $record->update(['estado' => 'activa']);
                        \Filament\Notifications\Notification::make()
                            ->title('Usuario activado')
                            ->body("La cuenta de {$record->correo} ha sido reactivada.")
                            ->success()
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
            'index' => Pages\ListUsuarios::route('/'),
        ];
    }
}
