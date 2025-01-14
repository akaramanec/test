<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\VisitorController;
use App\Http\Controllers\Bot\HookController;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => [
        'api',
    ],
    'prefix' => 'bot'
], function () {
    Route::post('telegram', [HookController::class, 'telegram']);
});
Route::group([
    'middleware' => [
        'api',
        'api.log.requests',
        'auth.token',
    ],
    'prefix' => 'v1'
], function () {
    Route::group(['prefix' => 'visitor'], function () {
        Route::post('in-establishment', [VisitorController::class, 'inEstablishment']);
    });
    Route::get('test', [HookController::class, 'test']);
});
