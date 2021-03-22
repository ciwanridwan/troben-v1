<?php

namespace App\Http\Middleware\Partner;

use App\Supports\Repositories\PartnerRepository;
use Closure;
use Illuminate\Http\Request;

class EnsureUserHasPartnerWithType
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param mixed ...$types
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$types)
    {
        $this->getPartnerRepository()->isAuthorizeByTypes($types);

        return $next($request);
    }

    private function getPartnerRepository(): PartnerRepository
    {
        return app(PartnerRepository::class);
    }
}
