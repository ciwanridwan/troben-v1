<?php

namespace App\Http\Middleware\Partner;

use App\Supports\Repositories\PartnerRepository;
use Closure;
use Illuminate\Http\Request;

class ScopeRole
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $role)
    {
        $this->getPartnerRepository()->setScopeRole($role);

        return $next($request);
    }

    private function getPartnerRepository(): PartnerRepository
    {
        return app(PartnerRepository::class);
    }
}
