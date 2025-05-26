<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmployerController;
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
    Route::post('info', [HookController::class, 'telegram']);
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
        Route::post('in', [VisitorController::class, 'in'])->name('visitor.in');
        Route::post('late', [VisitorController::class, 'late'])->name('visitor.late');
        Route::post('reject', [VisitorController::class, 'reject'])->name('visitor.reject');
    });
    Route::group(['prefix' => 'order'], function () {
        Route::post('add', [OrderController::class, 'add'])->name('order.add');
        Route::post('pay', [OrderController::class, 'pay'])->name('order.pay');
        Route::post('paid', [OrderController::class, 'paid'])->name('order.paid');
        Route::post('call', [OrderController::class, 'call'])->name('order.call');
        Route::post('evaluate', [OrderController::class, 'evaluate'])->name('order.evaluate');
    });
    Route::group(['prefix' => 'employer'], function () {
        Route::post('update', [EmployerController::class, 'update'])->name('employer.update');
        Route::delete('delete', [EmployerController::class, 'destroy'])->name('employer.delete');
    });
    Route::get('test', [HookController::class, 'test']);
});
