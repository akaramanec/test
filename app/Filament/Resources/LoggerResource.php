<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LoggerResource\Pages;
use App\Filament\Resources\LoggerResource\RelationManagers;
use App\Models\Logger;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn\TextColumnSize;
use Filament\Tables\Table;

class LoggerResource extends Resource
{
    protected static ?string $model = Logger::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Сервіс';
    protected static ?string $navigationLabel = 'Логи';

    protected static ?string $pluralModelLabel = 'Логи';
    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\ViewField::make('data')->view('components.formatted-data-column'),
                Forms\Components\TextInput::make('slug')
                    ->maxLength(255),
                Forms\Components\TextInput::make('platform_id')
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('data')
                    ->width('50%')
                    ->label('Дані')
                    ->view('components.formatted-data-column')
                    ->sortable(false),
                Tables\Columns\TextColumn::make('slug')
                    ->label('Слаг')
                    ->size(TextColumnSize::ExtraSmall)
                    ->searchable(),
                Tables\Columns\TextColumn::make('platform_id')
                    ->label('Platform ID')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Створено')
                    ->size(TextColumnSize::ExtraSmall)
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->hiddenLabel(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->paginationPageOptions([50, 100, 200])
            ->striped()
            ->defaultSort('id', 'desc');
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
            'index' => Pages\ListLoggers::route('/'),
            'show' => Pages\EditLogger::route('/{record}/edit'),
        ];
    }
}
