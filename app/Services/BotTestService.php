<?php

namespace App\Services;

use App\Bot\TmInit;
use App\Models\Logger;
use Illuminate\Http\Request;

class BotTestService
{
    public $log;

    public function __construct(Request $request)
    {
        if (!$request->id) {
            dd('No id selected');
        }
        $this->log = Logger::find($request->id);
        if (!$this->log) {
            dd('No log found');
        }
        $tmInit = app(TmInit::class);
        $tmInit->run(json_decode(json_encode($this->log->data)));

    }
}
