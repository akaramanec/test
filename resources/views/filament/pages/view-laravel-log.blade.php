<?php

use Illuminate\Support\Facades\File;

$logPath = storage_path('logs/laravel.log');
$fileContent = File::exists($logPath) ? File::get($logPath) : 'Log file not found.';

?>

<x-filament::page>
    <div class="filament-page">
        <h1 class="text-2xl font-bold mb-4">Application Log</h1>
        <pre class="bg-gray-100 p-4 rounded overflow-auto" style="background: black; color: #fbbf24">{{ $fileContent }}</pre>
    </div>
</x-filament::page>
