<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\Response;
use App\Exceptions\Error;
use Illuminate\Http\Request;
use App\Models\Customers\Customer;

class IsUsers
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
        throw_if($request->user() instanceof Customer, Error::class, Response::RC_UNAUTHORIZED);

        return $next($request);
    }
}
