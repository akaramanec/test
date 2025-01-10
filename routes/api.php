<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Bot\HookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::group([
    'middleware' => 'api',
    'prefix' => 'v1/auth'
], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('me', [AuthController::class, 'me']);
});
Route::post('/telegram', [HookController::class, 'telegram']);
Route::group([
    'middleware' => [
        'api',
        'api.log.requests'
    ],
    'prefix' => 'v1'
], function () {
    Route::group([
        'prefix' => 'auth'
    ], function () {
        Route::post('/', [AuthController::class, 'auth']);
    });
});
