<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserHasPartnerWithType
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param mixed ...$types
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$types)
    {
        return $next($request);
    }
}
