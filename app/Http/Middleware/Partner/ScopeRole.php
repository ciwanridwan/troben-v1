<?php

namespace App\Http\Middleware\Partner;

use Closure;
use Illuminate\Http\Request;
use App\Supports\Repositories\PartnerRepository;

class ScopeRole
{
    private PartnerRepository $repository;

    public function __construct(PartnerRepository $repository)
    {
        $this->repository = $repository;
    }

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
        $this->repository->setScopeRole($role);

        return $next($request);
    }
}
