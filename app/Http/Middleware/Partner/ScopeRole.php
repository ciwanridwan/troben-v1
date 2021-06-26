<?php

namespace App\Http\Middleware\Partner;

use Closure;
use Illuminate\Http\Request;
use App\Supports\Repositories\PartnerRepository;
use Illuminate\View\View;

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
        view()->composer(
            '*',
            function (View $view) {
                if (array_key_exists('laravelJs', $view->getData())) {
                    $view->with('laravelJs', array_merge($view->getData()['laravelJs'], ['role' => $this->repository->scopedRole]));
                }
            }
        );

        return $next($request);
    }
}
