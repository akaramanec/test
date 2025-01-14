<?php

namespace App\Jobs;

use App\Models\Bot\Customer;
use App\Services\Tabster\TabsterService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AdminNotifyJob implements ShouldQueue
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
        $reserve = TabsterService::composeReserveData($this->data);
        if (isset($reserve['admin_id'])) {
            $admin = Customer::where('external_id', $reserve['admin_id'])->first();
        } else {
            $admin = Customer::whereIn('external_id', $reserve['worker_ids'])->where('role', Customer::ROLE_ADMIN)->first();
        }
        /** @var Customer $waiter */
        if ($admin) {
            $bot = $admin->getBot();
            match ($this->type) {
                'late' => $bot->visitorLate($reserve),
                'reject' => $bot->visitorReject($reserve),
            };
        }
    }
}
