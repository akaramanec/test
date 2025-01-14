<?php

namespace App\Jobs;

use App\Models\Bot\Customer;
use App\Services\Tabster\TabsterService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class VisitorJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $data;
    protected string $type;

    public function __construct(array $data, $type)
    {
        $this->data = $data;
        $this->type = $type;
    }

    public function handle()
    {
        $order = TabsterService::composeOrderData($this->data);
        $waiter = Customer::where('external_id', $order['waiter_id'])->first();
        /** @var Customer $waiter */
        if ($waiter) {
            $bot = $waiter->getBot();
            match ($this->type) {
                'evaluate' => $bot->visitorEvaluate($order),
            };
        }
    }
}
