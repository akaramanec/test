<?php

namespace App\Jobs;

use App\Models\Bot\Employer;
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
            Employer::updateWorkers($this->data['workers']);
        }

        /** @var Employer $employer */
        $employer = Employer::where('external_id', $this->data['waiter_id'] ?? $this->data['admin_id'] ?? null)->first();
        if (!$employer && $this->type == 'call') {
            $admins = $this->data['workers']['admins'] ?? [];
            foreach ($admins as $adminData) {
                /** @var Employer $admin */
                if (!$admin = Employer::where('external_id', $adminData['id'])->first()) continue;
                $adminBot = $admin->getBot();
                $adminBot->orderCallWithoutWaiter($this->data);
            }
        } else {
            if (in_array($this->type, ['pay', 'paid', 'add', 'evaluate'])) {
                $admins = $this->data['workers']['admins'] ?? [];
                foreach ($admins as $adminData) {
                    /** @var Employer $admin */
                    if (!$admin = Employer::where('external_id', $adminData['id'])->first()) continue;
                    if ($admin->id == $employer?->id) continue;
                    $waiter = $employer ? "Офіціант: $employer->name" : "";
                    $additionalPlaceholders = ['{waiter}' => $waiter];
                    $this->processedForEmployer($admin, $additionalPlaceholders);
                }
            }

            if ($employer) {
                if ($this->type == 'evaluate') {
                    $additionalPlaceholders = ['{waiter}' => ""];
                }
                $this->processedForEmployer($employer, $additionalPlaceholders ?? []);
            }
        }
    }

    public function processedForEmployer(Employer $employer, $additionalPlaceholders = []): void
    {
        $bot = $employer->getBot();
        match ($this->type) {
            'pay' => $bot->orderPay($this->data),
            'paid' => $bot->orderPaid($this->data),
            'add' => $bot->orderAdditional($this->data),
            'call' => $bot->orderCall($this->data),
            'evaluate' => $bot->visitorEvaluate($this->data, $additionalPlaceholders),
        };
    }
}
