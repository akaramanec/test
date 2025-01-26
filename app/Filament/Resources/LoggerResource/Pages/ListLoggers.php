<?php

namespace App\Filament\Resources\LoggerResource\Pages;

use App\Filament\Resources\LoggerResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListLoggers extends ListRecords
{
    protected static string $resource = LoggerResource::class;


    protected function getHeaderActions(): array
    {
        return [
            Action::make('Open Bot Info')
                ->url(route('bot.info'))
                ->label('Bot Info')
                ->color('primary')
                ->openUrlInNewTab(),

//                ->form([
//                    // Поля модального вікна
//                    View::make('bot.info-modal'),
//                ])
//                ->modalHeading('Bot Info')
//                ->modalSubmitActionLabel('Close')
//                ->translateLabel()
//                ->modalWidth('lg'),
        ];
    }
}
