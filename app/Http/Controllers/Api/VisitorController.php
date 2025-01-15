<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EvaluationRequest;
use App\Http\Requests\InEstablishmentRequest;
use App\Http\Requests\VisitorRequest;
use App\Jobs\AdminNotifyJob;
use App\Jobs\InEstablishmentJob;
use App\Jobs\VisitorJob;
use App\Models\Project\Notification;
use Illuminate\Http\Response;

class VisitorController extends Controller
{
    public function in(InEstablishmentRequest $request)
    {
        $key = "$request->reserve_id-" . today()->toDateString();
        if (Notification::where('key', $key)->exists()) {
            return response()->json(['status' => 'ok'], Response::HTTP_OK);
        }
        try {
            $this->notification = Notification::create([
                'key' => $key,
                'action' => 'inEstablishment',
                'status' => 'new',
                'data' => $request->all()
            ]);
            InEstablishmentJob::dispatch($this->notification, config('app.url') === 'https://base-bot.boto.kyiv.ua');
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'trace' => $e->getTrace()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return response()->json(['status' => 'ok'], Response::HTTP_CREATED);
    }

    public function evaluate(EvaluationRequest $request)
    {
        VisitorJob::dispatch($request->all(), 'evaluate');
        return response()->json(['status' => 'ok'], Response::HTTP_OK);
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
