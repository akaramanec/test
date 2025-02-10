<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JobResource\Pages;
use App\Models\Job;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;

class JobResource extends Resource
{
    protected static ?string $model = Job::class;
    protected static ?string $navigationIcon = 'heroicon-o-queue-list';
    protected static ?string $label = 'Активні черги';

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID'),
                TextColumn::make('queue')->label('Черга'),
                TextColumn::make('payload')->label('Дані'),

                TextColumn::make('created_at')->label('Дата')->dateTime(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJobs::route('/'),
        ];
    }
}

