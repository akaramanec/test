<?php

namespace App\Jobs;

use App\Models\Bot\Employer;
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
        $waiter = Employer::where('external_id', $this->data['assigned_waiter_id'])->first();
        /** @var Employer $waiter */
        if ($waiter) {
            $bot = $waiter->getBot();
            match ($this->type) {
                'evaluate' => $bot->visitorEvaluate($this->data),
            };
        }
    }
}
