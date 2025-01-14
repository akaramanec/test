<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Jobs\OrderJob;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function add(OrderRequest $request)
    {
        OrderJob::dispatch($request->all(), 'add');
    }

    public function pay(OrderRequest $request)
    {
        OrderJob::dispatch($request->all(), 'pay');
    }

    public function paid(OrderRequest $request)
    {
        OrderJob::dispatch($request->all(), 'paid');
    }

    public function call(OrderRequest $request)
    {
        OrderJob::dispatch($request->all(), 'call');
    }
}
