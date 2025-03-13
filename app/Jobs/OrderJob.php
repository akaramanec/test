<?php

namespace App\Jobs;

use App\Models\Bot\Customer;
use App\Models\Logger;
use App\Services\Tabster\TabsterService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OrderJob implements ShouldQueue
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
        if (isset($this->data['workers'])) {
            Customer::updateWorkers($this->data['workers']);
        }

        $waiter = Customer::where('external_id', $this->data['waiter_id'])->first();
        /** @var Customer $waiter */
        if ($waiter) {
            $bot = $waiter->getBot();
            match ($this->type) {
                'pay' => $bot->orderPay($this->data),
                'paid' => $bot->orderPaid($this->data),
                'add' => $bot->orderAdditional($this->data),
                'call' => $bot->orderCall($this->data),
                'evaluate' => $bot->visitorEvaluate($this->data),
            };
        }
    }
}
