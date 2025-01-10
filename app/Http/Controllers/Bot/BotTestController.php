<?php

namespace App\Http\Controllers\Bot;

use App\Http\Controllers\Controller;
use App\Services\BotTestService;
use Illuminate\Http\Request;

class BotTestController extends Controller
{
    public function __invoke(Request $request)
    {
        new BotTestService($request);
    }
}
