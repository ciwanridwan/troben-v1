<?php

namespace App\Http\Middleware\Partner;

use App\Supports\Repositories\PartnerRepository;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
        abort_if(! $this->getPartnerRepository()->isAuthorizeByRoles($roles), Response::HTTP_FORBIDDEN);

        return $next($request);
    }

    private function getPartnerRepository(): PartnerRepository
    {
        return app(PartnerRepository::class);
    }
}
