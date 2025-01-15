<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;
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
        Route::post('in', [VisitorController::class, 'in']);
        Route::post('evaluate', [VisitorController::class, 'evaluate']);
        Route::post('late', [VisitorController::class, 'late']);
        Route::post('reject', [VisitorController::class, 'reject']);
    });
    Route::group(['prefix' => 'order'], function () {
        Route::post('add', [OrderController::class, 'add']);
        Route::post('pay', [OrderController::class, 'pay']);
        Route::post('paid', [OrderController::class, 'paid']);
        Route::post('call', [OrderController::class, 'call']);
    });
    Route::get('test', [HookController::class, 'test']);
});
