<?php

namespace App\Console\Commands;

use App\Models\Project\Notification;
use App\Services\Project\InEstablishmentService;
use Illuminate\Console\Command;

class CheckInEstablishmentAssignConsole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:in-establishment-assign';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checking whether the waiter is assigned a visitor\'s table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('CheckInEstablishmentAssignConsole');
        $notifications = Notification::where('action', 'inEstablishment')->where('status',  Notification::STATUS_NEW);
        if (!$notifications->exists()) {
            $this->info('No new notifications');
            return;
        }

        $notificationIds = $notifications->pluck('id');
//        Notification::whereIn('id', $notificationIds)->update(['status' => Notification::STATUS_PROCESSING]);

        /** @var Notification $notification */
        foreach (Notification::whereIn('id', $notificationIds)->get() as $notification) {
            $this->info('Processing notification: ' . $notification->id);
            InEstablishmentService::deleteMessages($notification);
            InEstablishmentService::sendMessages($notification);
            $notification->update(['status' => 'new']);
            $this->info('Notification resent');
        }
    }
}
