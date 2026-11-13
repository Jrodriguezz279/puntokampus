<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PuntoEntregaResource\Pages;
use App\Filament\Resources\PuntoEntregaResource\RelationManagers;
use App\Models\PuntoEntrega;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PuntoEntregaResource extends Resource
{
    protected static ?string $model = PuntoEntrega::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('descripcion')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('activa')
                    ->required(),
                Forms\Components\TextInput::make('orden')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\DateTimePicker::make('creado_en'),
                Forms\Components\DateTimePicker::make('actualizado_en'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable(),
                Tables\Columns\IconColumn::make('activa')
                    ->boolean(),
                Tables\Columns\TextColumn::make('orden')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('creado_en')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('actualizado_en')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPuntoEntregas::route('/'),
            'create' => Pages\CreatePuntoEntrega::route('/create'),
            'edit' => Pages\EditPuntoEntrega::route('/{record}/edit'),
        ];
    }
}
