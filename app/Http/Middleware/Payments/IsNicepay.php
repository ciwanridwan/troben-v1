<?php

namespace App\Http\Middleware\Payments;

use App\Exceptions\Error;
use App\Exceptions\UserUnauthorizedException;
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
        throw_if($request->header('user-agent') !== config('nicepay.nicepay_headers.user-agent'), new UserUnauthorizedException(Response::RC_UNAUTHORIZED));

        return $next($request);
    }
}
