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

class PreOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $data;

    public function __construct(array $data,)
    {
        $this->data = $data;
    }

    public function handle()
    {
        if (isset($this->data['workers'])) {
            Employer::updateWorkers($this->data['workers']);
        }

        $admins = $this->data['workers']['admins'] ?? [];
        foreach ($admins as $adminData) {
            /** @var Employer $admin */
            if (!$admin = Employer::where('external_id', $adminData['id'])->first()) continue;
            $adminBot = $admin->getBot();
            $adminBot->preOrder($this->data);
        }
    }
}
