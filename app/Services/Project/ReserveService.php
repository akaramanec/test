<?php

namespace App\Services\Project;

use App\Http\Requests\ReserveRequest;
use App\Models\Bot\Employer;
use App\Models\Project\Reserve;
use Carbon\Carbon;
use Illuminate\Http\Response;

class ReserveService
{
    public static function handleReservation(ReserveRequest $request)
    {
        $key = "$request->reservation_id-" . today()->toDateString();
        if (Reserve::where('key', $key)->exists()) {
            return response()->json(['status' => 'ok'], Response::HTTP_OK);
        }
        try {
            Reserve::create([
                'key' => $key,
                'status' => 'new',
                'date' => Carbon::parse($request->time)->format('Y-m-d'),
                'data' => $request->all()
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'trace' => $e->getTrace()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return response()->json(['status' => 'ok'], Response::HTTP_CREATED);
    }

    public static function sendMessages(Reserve $reserve)
    {
        Employer::updateWorkers($reserve->data['workers']);
        $admins = $reserve->data['workers']['admins'];
        foreach ($admins as $admin) {
            if (!$admin = Employer::where('external_id', $admin['id'])->first()) continue;
            $bot = $admin->getBot();
            $bot->reserve($reserve->data);
        }
        $reserve->update(['sent_at' => now()]);
        $reserve->refresh();
    }

    public static function saveMessageId(Reserve $reserve, $customerId, $messageId)
    {
        $messageIds = $notification->message_ids ?? [];
        $messageIds[$customerId] = $messageId;
        $reserve->update(['message_ids' => $messageIds]);
        $reserve->refresh();
    }
}
