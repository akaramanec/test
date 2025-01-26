<?php

namespace App\Jobs;

use App\Models\Bot\Customer;
use App\Models\Project\Notification;
use App\Services\Project\InEstablishmentService;
use App\Services\Tabster\TabsterService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class InEstablishmentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Notification $notification;
    protected ?bool $fake;

    public function __construct(Notification $notification, $fake = null)
    {
        $this->notification = $notification;
        $this->fake = $fake;
    }

    public function handle()
    {
        InEstablishmentService::sendMessages($this->notification);
    }
}
