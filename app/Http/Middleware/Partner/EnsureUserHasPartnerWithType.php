<?php

namespace App\Http\Middleware\Partner;

use App\Supports\Repositories\PartnerRepository;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
        abort_if(! $this->getPartnerRepository()->isAuthorizeByTypes($types), Response::HTTP_FORBIDDEN);

        return $next($request);
    }

    private function getPartnerRepository(): PartnerRepository
    {
        return app(PartnerRepository::class);
    }
}
