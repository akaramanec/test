<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureTokenIsValid
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->bearerToken() !== config('app.token_api')) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        return $next($request);
    }
}
