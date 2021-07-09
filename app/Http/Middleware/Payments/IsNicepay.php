<?php

namespace App\Http\Middleware\Payments;

use App\Exceptions\Error;
use App\Http\Response;
use Closure;
use Illuminate\Http\Request;

class IsNicepay
{
    /**
     * Handle an incoming request for notification by nicepay.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        throw_if(! in_array($request->ip(), config('nicepay.nicepay_server')), new Error(Response::RC_UNAUTHORIZED));

        return $next($request);
    }
}
