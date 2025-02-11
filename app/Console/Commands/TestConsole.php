<?php

namespace App\Console\Commands;

use App\Models\Bot\Customer;
use App\Services\Tabster\TabsterService;
use Illuminate\Console\Command;

class TestConsole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        dd(TabsterService::getWorker(getFullPhoneFormat('+380989165575')));
    }
}
