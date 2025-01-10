<?php

namespace App\Http\Middleware;

use App\Models\Logger;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LogRequests
{
    public function handle(Request $request, Closure $next)
    {
        $data = ['url' => $request->url(), 'method' => $request->method(), 'data' => $request->all()];
        if ($request->user()) {
            $data['customer'] = [
                'id' => $request->user()->id,
                'platform_id' => $request->user()->platform_id,
                'phone' => $request->user()->phone,
                'first_name' => $request->user()->first_name,
                'last_name'  => $request->user()->last_name
            ];
        }

        Logger::commit($data, 'API request log');
        return $next($request);
    }
}
