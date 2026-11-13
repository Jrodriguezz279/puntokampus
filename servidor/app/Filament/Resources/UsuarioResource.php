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
