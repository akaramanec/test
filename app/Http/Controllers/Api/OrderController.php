<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Jobs\OrderJob;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OrderController extends Controller
{
    public function add(OrderRequest $request)
    {
        OrderJob::dispatch($request->all(), 'add');
        return response()->json(['status' => 'ok'], Response::HTTP_OK);
    }

    public function pay(OrderRequest $request)
    {
        OrderJob::dispatch($request->all(), 'pay');
        return response()->json(['status' => 'ok'], Response::HTTP_OK);
    }

    public function paid(OrderRequest $request)
    {
        OrderJob::dispatch($request->all(), 'paid');
        return response()->json(['status' => 'ok'], Response::HTTP_OK);
    }

    public function call(OrderRequest $request)
    {
        OrderJob::dispatch($request->all(), 'call');
        return response()->json(['status' => 'ok'], Response::HTTP_OK);
    }
}
