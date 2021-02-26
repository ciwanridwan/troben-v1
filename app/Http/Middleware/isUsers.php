<?php

namespace App\Http\Middleware;

use App\Exceptions\Error;
use App\Http\Response;
use App\Models\Customers\Customer;
use Closure;
use Illuminate\Http\Request;

class isUsers
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
        throw_if($request->user() instanceof Customer, Error::make(Response::RC_UNAUTHORIZED));

        return $next($request);
    }
}
