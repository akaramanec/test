<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FailedJobResource\Pages;
use App\Models\FailedJob;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;

class FailedJobResource extends Resource
{
    protected static ?string $model = FailedJob::class;
    protected static ?string $navigationIcon = 'heroicon-o-x-circle';
    protected static ?string $label = 'Помилки черг';

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID'),
                TextColumn::make('connection')->label('Підключення'),
                TextColumn::make('queue')->label('Черга'),
                Tables\Columns\TextColumn::make('exception')
                    ->label('Помилка')
                    ->view('components.formatted-data-column')
                    ->sortable(false),
                TextColumn::make('failed_at')->label('Дата')->dateTime(),
            ])
            ->defaultSort('failed_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFailedJobs::route('/'),
        ];
    }
}

