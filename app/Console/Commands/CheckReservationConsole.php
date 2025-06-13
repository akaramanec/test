<?php

namespace App\Console\Commands;

use App\Models\Project\Notification;
use App\Models\Project\Reserve;
use App\Services\Project\InEstablishmentService;
use App\Services\Project\ReserveService;
use Illuminate\Console\Command;

class CheckReservationConsole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:reservation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checking is there any new reservation and send messages to admins if there are any';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for new reservations...');
        $reserve = Reserve::where('status', Reserve::STATUS_NEW)
            ->where('date', '=', today()->format('Y-m-d'));
        if (!$reserve->exists()) {
            $this->info('No new reserve');
            return;
        }

        $reserveIds = $reserve->pluck('id');
        Reserve::whereIn('id', $reserveIds)->update(['status' => Reserve::STATUS_PROCESSING]);
            /** @var Reserve $reserve */
            foreach (Reserve::whereIn('id', $reserveIds)->get() as $reserve) {
                try {
                $this->info('Processing reserve: ' . $reserve->id);
                ReserveService::sendMessages($reserve);
                $reserve->update(['status' => Reserve::STATUS_PROCESSED]);
                $this->info('Reserve sent to admins: ' . $reserve->id);
                } catch (\Exception $e) {
                    $reserve->update(['status' => Reserve::STATUS_NEW]);
                    throw new $e;
                }
            }
    }
}
