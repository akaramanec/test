<?php

namespace App\Services\Project;

use App\Http\Requests\VisitorRequest;
use App\Models\Bot\Employer;
use App\Models\Project\Notification;
use Illuminate\Http\Response;

class InEstablishmentService
{
    public static function handleInEstablishment(VisitorRequest $request)
    {

        $key = "$request->reservation_id-" . today()->toDateString();
        if (Notification::where('key', $key)->exists()) {
            return response()->json(['status' => 'ok'], Response::HTTP_OK);
        }
        try {
            $notification = Notification::create([
                'key' => $key,
                'action' => 'inEstablishment',
                'status' => 'new',
                'data' => $request->all()
            ]);
            self::sendMessages($notification);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'trace' => $e->getTrace()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return response()->json(['status' => 'ok'], Response::HTTP_CREATED);
    }

    public static function sendMessages(Notification $notification)
    {
        $workers = $notification->data['workers'];
        $allIds = [];
        foreach ($workers as $role => $workerGroup) {
            $ids = array_column($workerGroup, 'id');
            $allIds = array_merge($allIds, $ids);
        }
        $workers = Employer::whereIn('external_id', $allIds)->get();
        /** @var Employer $worker */
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
                $worker = Employer::find($workerId);
                $bot = $worker->getBot();
                $bot->deleteMessageByMessageId($messageId);
            }
        }
        $notification->update(['message_ids' => []]);
        $notification->refresh();
    }

    public static function deleteMessagesExceptWaiter(Notification $notification, $waiterId)
    {
        $messageIds = $notification->message_ids;

        if ($messageIds) {
            foreach ($messageIds as $workerId => $messageId) {
                if ($workerId != $waiterId) {
                    $worker = Employer::find($workerId);
                    $bot = $worker->getBot();
                    $bot->deleteMessageByMessageId($messageId);

                    unset($messageIds[$workerId]);
                }
            }

            $notification->update(['message_ids' => $messageIds]);
            $notification->refresh();
        }
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
