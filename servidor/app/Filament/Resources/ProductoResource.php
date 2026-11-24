<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductoResource\Pages;
use App\Models\Producto;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;

class ProductoResource extends Resource
{
    protected static ?string $model = Producto::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationLabel = 'Productos';

    protected static ?string $pluralModelLabel = 'Productos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->maxLength(50),
                Forms\Components\Select::make('tienda_id')
                    ->relationship('tienda', 'nombre')
                    ->required()
                    ->disabled(),
                Forms\Components\TextInput::make('precio')
                    ->numeric()
                    ->prefix('$')
                    ->required(),
                Forms\Components\TextInput::make('precio_descuento')
                    ->numeric()
                    ->prefix('$'),
                Forms\Components\Toggle::make('descuento_activo')
                    ->label('Descuento activo'),
                Forms\Components\Toggle::make('disponible')
                    ->label('Disponible'),
                Forms\Components\Textarea::make('descripcion')
                    ->maxLength(200)
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
                Tables\Columns\TextColumn::make('tienda.nombre')
                    ->label('Tienda')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('precio')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('precio_descuento')
                    ->label('Rebajado')
                    ->money('USD')
                    ->placeholder('N/A'),
                Tables\Columns\IconColumn::make('descuento_activo')
                    ->label('Descuento')
                    ->boolean(),
                Tables\Columns\IconColumn::make('disponible')
                    ->label('Disponible')
                    ->boolean(),
                Tables\Columns\TextColumn::make('creado_en')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Action::make('alternar_disponibilidad')
                    ->label(fn (Producto $record): string => $record->disponible ? 'Desactivar' : 'Activar')
                    ->icon(fn (Producto $record): string => $record->disponible ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (Producto $record): string => $record->disponible ? 'warning' : 'success')
                    ->requiresConfirmation()
                    ->action(function (Producto $record): void {
                        $record->update(['disponible' => !$record->disponible]);
                        Notification::make()
                            ->title('Disponibilidad actualizada')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar')
                    ->requiresConfirmation(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductos::route('/'),
        ];
    }
}
