<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConfiguracionResource\Pages;
use App\Models\Configuracion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ConfiguracionResource extends Resource
{
    protected static ?string $model = Configuracion::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Configuración General';

    protected static ?string $modelLabel = 'Configuración';

    protected static ?string $pluralModelLabel = 'Configuraciones';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Redes Sociales Oficiales')
                    ->description('Enlaces a las redes sociales de PuntoKampus que se mostrarán en la vitrina.')
                    ->schema([
                        Forms\Components\TextInput::make('instagram_url')
                            ->label('Instagram URL')
                            ->url()
                            ->placeholder('https://instagram.com/puntokampus'),
                        Forms\Components\TextInput::make('tiktok_url')
                            ->label('TikTok URL')
                            ->url()
                            ->placeholder('https://tiktok.com/@puntokampus'),
                        Forms\Components\TextInput::make('facebook_url')
                            ->label('Facebook URL')
                            ->url()
                            ->placeholder('https://facebook.com/puntokampus'),
                    ])->columns(3),

                Forms\Components\Section::make('Datos de Cobro Premium')
                    ->description('Parámetros para la suscripción premium de tiendas.')
                    ->schema([
                        Forms\Components\TextInput::make('precio_premium')
                            ->label('Precio Premium ($)')
                            ->numeric()
                            ->prefix('$'),
                        Forms\Components\TextInput::make('banco')
                            ->label('Banco receptor'),
                        Forms\Components\TextInput::make('tipo_cuenta')
                            ->label('Tipo de cuenta'),
                        Forms\Components\TextInput::make('numero_cuenta')
                            ->label('Número de cuenta'),
                        Forms\Components\TextInput::make('titular_cuenta')
                            ->label('Titular de cuenta'),
                        Forms\Components\TextInput::make('usuario_deuna')
                            ->label('Usuario DeUna'),
                    ])->columns(2),

                Forms\Components\Section::make('Límites y Periodo')
                    ->schema([
                        Forms\Components\DatePicker::make('fin_periodo_academico')
                            ->label('Fin periodo académico'),
                        Forms\Components\TextInput::make('limite_pedidos_gratis')
                            ->label('Límite pedidos gratis')
                            ->numeric(),
                        Forms\Components\TextInput::make('dias_borrado_comprobante')
                            ->label('Días borrado comprobantes')
                            ->numeric()
                            ->default(30),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('instagram_url')
                    ->label('Instagram')
                    ->limit(25),
                Tables\Columns\TextColumn::make('tiktok_url')
                    ->label('TikTok')
                    ->limit(25),
                Tables\Columns\TextColumn::make('facebook_url')
                    ->label('Facebook')
                    ->limit(25),
                Tables\Columns\TextColumn::make('precio_premium')
                    ->label('Precio Premium')
                    ->money('USD'),
                Tables\Columns\TextColumn::make('actualizado_en')
                    ->label('Última actualización')
                    ->dateTime(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConfiguracions::route('/'),
            'create' => Pages\CreateConfiguracion::route('/create'),
            'edit' => Pages\EditConfiguracion::route('/{record}/edit'),
        ];
    }
}
