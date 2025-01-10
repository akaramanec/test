<?php

namespace App\Http\Controllers\Bot;

use App\Bot\TmInit;
use App\Http\Controllers\Controller;
use App\Models\Logger;

class HookController extends Controller
{
    public function telegram(TmInit $tmInit)
    {
        $input = file_get_contents('php://input');
        Logger::commit($input, __METHOD__);
        $tmInit->run(json_decode($input));
    }

}
