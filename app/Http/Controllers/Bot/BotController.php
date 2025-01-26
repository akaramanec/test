<?php

namespace App\Http\Controllers\Bot;

use App\Bot\TmInfo;
use App\Http\Controllers\Controller;
use App\Services\BotTestService;
use Illuminate\Http\Request;

class BotController extends Controller
{
    public function make(Request $request)
    {
        new BotTestService($request);
    }

    public function info(TmInfo $info)
    {
        dd(['getMe' => $info->getMe['result'], 'webhookInfo' => $info->webHookInfo['result']]);
    }

    public function test()
    {
        dd('test');
    }
}
