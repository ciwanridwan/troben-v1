<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CheckUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        abort_if(! $request->user(), Response::HTTP_UNAUTHORIZED);
        abort_if(! $request->user()->is_admin, Response::HTTP_FORBIDDEN);

        return $next($request);
    }
}
