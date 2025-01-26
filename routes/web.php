<?php

use App\Http\Controllers\Bot\BotController;
use App\Http\Controllers\Bot\HomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/bot/make', [BotController::class, 'make'])->name('bot.make');
Route::get('/bot/info', [BotController::class, 'info'])->name('bot.info');
Route::get('/test', [HomeController::class, 'test'])->name('bot.test');
