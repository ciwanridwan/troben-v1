<?php

namespace App\Http\Middleware\Partner;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Supports\Repositories\PartnerRepository;

class EnsureUserHasPartnerWithRole
{

    /**
     * @var \App\Supports\Repositories\PartnerRepository
     */
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
     * @param mixed ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        abort_if(! $this->repository->isAuthorizeByRoles($roles), Response::HTTP_FORBIDDEN);

        return $next($request);
    }
}
