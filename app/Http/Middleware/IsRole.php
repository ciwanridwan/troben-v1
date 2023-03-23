<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\Response;
use App\Exceptions\Error;
use App\Exceptions\UserUnauthorizedException;
use Illuminate\Http\Request;
use App\Models\Partners\Pivot\UserablePivot;

class IsRole
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param $roleName
     * @return mixed
     * @throws \Throwable
     */
    public function handle(Request $request, Closure $next, $roleName)
    {
        $partners = $request->user()->partners;

        if ($roleName === 'admin') {
            if ($partners) {
                if ($request->expectsJson()) {
                    throw UserUnauthorizedException::make(Response::RC_UNAUTHORIZED);
                }

                $role = $partners->pluck('pivot.role')->first();

                return redirect(UserablePivot::getHomeRouteRole($role));
            }

            return $next($request);
        }

        // if ($partners) {
        //     $roles = $partners->pluck('pivot.role');
        //     if (!in_array($role_name, $roles->toArray())) {
        //         if ($request->expectsJson()) {
        //             throw_if(true, Error::make(Response::RC_UNAUTHORIZED));
        //         }
        //         return redirect(UserablePivot::getHomeRouteRole($roles->first()));
        //     }
        // }
        return $next($request);
    }
}
