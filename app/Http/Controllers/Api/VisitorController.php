<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\VisitorRequest;
use App\Jobs\AdminNotifyJob;
use App\Models\Project\Notification;
use App\Services\Project\InEstablishmentService;
use Illuminate\Http\Response;

class VisitorController extends Controller
{
    public function in(VisitorRequest $request)
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
            InEstablishmentService::sendMessages($notification);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'trace' => $e->getTrace()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return response()->json(['status' => 'ok'], Response::HTTP_CREATED);
    }

    public function late(VisitorRequest $request)
    {
        AdminNotifyJob::dispatch($request->all(), 'late');
        return response()->json(['status' => 'ok'], Response::HTTP_OK);
    }

    public function reject(VisitorRequest $request)
    {
        AdminNotifyJob::dispatch($request->all(), 'reject');
        return response()->json(['status' => 'ok'], Response::HTTP_OK);
    }
}
