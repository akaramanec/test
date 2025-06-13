<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PreOrderRequest;
use App\Http\Requests\ReserveRequest;
use App\Http\Requests\VisitorRequest;
use App\Jobs\AdminNotifyJob;
use App\Jobs\PreOrderJob;
use App\Services\Project\InEstablishmentService;
use App\Services\Project\ReserveService;
use Illuminate\Http\Response;

class VisitorController extends Controller
{
    public function in(VisitorRequest $request)
    {
        return InEstablishmentService::handleInEstablishment($request);
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

    public function preOrder(PreOrderRequest $request)
    {
        PreOrderJob::dispatch($request->all());
        return response()->json(['status' => 'ok'], Response::HTTP_OK);
    }

    public function reserve(ReserveRequest $request)
    {
        return ReserveService::handleReservation($request);
    }
}
