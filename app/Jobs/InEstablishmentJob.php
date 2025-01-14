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
        $visitor = TabsterService::getVisitor($this->notification->data['visitor_id'], $this->fake);
        $order = TabsterService::getOrder($this->notification->data['order_id'], $this->fake);
        $workerIds = TabsterService::getWorkers($this->notification->data['establishment_id'], $this->fake);

        $workers = Customer::whereIn('external_id', $workerIds)->get();
        $waiters = Customer::whereIn('external_id', $workerIds)->where('role', Customer::ROLE_WAITER)->get();

        $this->notification->update([
            'data' => [
                'visitor' => $visitor,
                'order' => $order,
                'waiters' => $waiters->pluck('id'),
                'workers' => $workers->pluck('id'),
                'come_time' => $this->notification->data['come_time']
            ]
        ]);
        $this->notification->refresh();
        InEstablishmentService::sendMessages($this->notification);
    }
}
