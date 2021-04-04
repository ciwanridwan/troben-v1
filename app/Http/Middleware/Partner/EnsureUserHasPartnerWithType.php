<?php

namespace App\Http\Middleware\Partner;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Supports\Repositories\PartnerRepository;

class EnsureUserHasPartnerWithType
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
     * @param mixed ...$types
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$types)
    {
        abort_if(! $this->repository->isAuthorizeByTypes($types), Response::HTTP_FORBIDDEN);

        return $next($request);
    }
}
