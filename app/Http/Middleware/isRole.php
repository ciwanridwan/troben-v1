<?php

namespace App\Http\Middleware;

use App\Exceptions\Error;
use App\Http\Response;
use App\Models\Partners\Pivot\UserablePivot;
use Closure;
use Illuminate\Http\Request;

class isRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $role_name)
    {

        if ($role_name == 'admin') {
            if ($request->user()->partners) {
                if ($request->expectsJson()) {
                    throw_if(true, Error::make(Response::RC_UNAUTHORIZED));
                }
                $role = $request->user()->partners->pluck('pivot.role')->first();
                return redirect(UserablePivot::getHomeRouteRole($role));
            }
        }

        throw_if($role_name, Error::make(Response::RC_UNAUTHORIZED));
        return $next($request);
    }
}
