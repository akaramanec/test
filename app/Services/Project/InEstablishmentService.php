<?php

namespace App\Services\Project;

use App\Models\Bot\Customer;
use App\Models\Bot\Text;
use App\Models\Project\Notification;

class InEstablishmentService
{

    public static function sendMessages(Notification $notification)
    {
        $workers = $notification->data['workers'];
        $allIds = [];
        foreach ($workers as $role => $workerGroup) {
            $ids = array_column($workerGroup, 'id');
            $allIds = array_merge($allIds, $ids);
        }
        $workers = Customer::whereIn('external_id', $allIds)->get();
        /** @var Customer $worker */
        foreach ($workers as $worker) {
            $bot = $worker->getBot();
            $bot->visitorIn($notification);
        }
        $notification->update(['sent_at' => now()]);
        $notification->refresh();
    }

    public static function deleteMessages(Notification $notification)
    {
        if ($notification->message_ids) {
            foreach ($notification->message_ids as $workerId => $messageId) {
                $worker = Customer::find($workerId);
                $bot = $worker->getBot();
                $bot->deleteMessageByMessageId($messageId);
            }
        }
        $notification->update(['message_ids' => []]);
        $notification->refresh();
    }

        public static function deleteMessageForWaiter(Notification $notification, $bot)
    {
        foreach ($notification->message_ids as $workerId => $messageId) {
            if ($workerId == $bot->init->customer->id) {
                $bot->deleteMessageByMessageId($messageId);
                $messageIds = $notification->message_ids;
                unset($messageIds[$workerId]);
                $notification->update(['message_ids' => $messageIds]);
                $notification->refresh();
            }
        }
    }

    public static function saveMessageId(Notification $notification, $customerId, $messageId)
    {
        $messageIds = $notification->message_ids ?? [];
        $messageIds[$customerId] = $messageId;
        $notification->update(['message_ids' => $messageIds]);
        $notification->refresh();
    }
}
