<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\File;

class ViewLaravelLog extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'View Laravel Log';
    protected static ?string $slug = 'view-laravel-log';
    protected static string $view = 'filament.pages.view-laravel-log';

    public function getLogContent(): string
    {
        $logPath = storage_path('logs/laravel.log');
        return File::exists($logPath) ? File::get($logPath) : 'Log file not found.';
    }
}
