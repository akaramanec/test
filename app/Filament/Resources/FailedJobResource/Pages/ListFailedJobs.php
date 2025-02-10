<?php

namespace App\Filament\Resources\FailedJobResource\Pages;

use App\Filament\Resources\FailedJobResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFailedJobs extends ListRecords
{
    protected static string $resource = FailedJobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
