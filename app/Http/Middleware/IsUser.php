<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\Response;
use App\Exceptions\UserUnauthorizedException;
use Illuminate\Http\Request;
use App\Models\Customers\Customer;

class IsUser
{
    /**
     * Filtering request for users.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     * @throws \Throwable
     */
    public function handle(Request $request, Closure $next)
    {
        /** @noinspection PhpParamsInspection */
        throw_if($request->user() instanceof Customer, UserUnauthorizedException::class, Response::RC_UNAUTHORIZED);

        return $next($request);
    }
}
