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
        if (isset($this->data['workers'])) {
            Customer::updateWorkers($this->data['workers']);
        }

        $admins = Customer::whereIn('external_id', array_column($this->data['workers']['admins'], 'id'))->get();
        /** @var Customer $admin */
        if ($admins) {
            foreach ($admins as $admin) {
                $bot = $admin->getBot();
                match ($this->type) {
                    'late' => $bot->visitorLate($this->data),
                    'reject' => $bot->visitorReject($this->data),
                };
            }
        }
    }
}
