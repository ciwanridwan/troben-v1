<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Partners\Pivot\UserablePivot;
use App\Supports\Repositories\PartnerRepository;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  ...$guards
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return redirect($request->user()->is_admin
                    ? route('admin.home')
                    : UserablePivot::getHomeRouteRole($this->getPartnerRepository()->getScopedRole()));
            }
        }

        return $next($request);
    }

    public function getPartnerRepository(): PartnerRepository
    {
        return app(PartnerRepository::class);
    }
}
