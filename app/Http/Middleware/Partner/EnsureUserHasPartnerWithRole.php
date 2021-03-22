<?php

namespace App\Http\Middleware\Partner;

use App\Supports\Repositories\PartnerRepository;
use Closure;
use Illuminate\Http\Request;

class EnsureUserHasPartnerWithRole
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param mixed ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $this->getPartnerRepository()->isAuthorizeByRoles($roles);

        return $next($request);
    }

    private function getPartnerRepository(): PartnerRepository
    {
        return app(PartnerRepository::class);
    }
}
