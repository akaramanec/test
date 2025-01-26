<?php

namespace App\Http\Controllers\Api;

use App\Bot\TmInfo;
use App\Http\Controllers\Controller;
use App\Services\BotTestService;
use Illuminate\Http\Request;

class BotController extends Controller
{
    public function info(TmInfo $info)
    {
        return response()->json(['getMe' => $info->getMe['result'], 'webhookInfo' => $info->webHookInfo['result']]);
    }
}
