<?php

namespace App\Supports\Repositories;

use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;

class PartnerRepository
{
    private Application $application;

    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    /**
     * get request from container
     *
     * @return \Illuminate\Http\Request
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function getRequest(): Request
    {
        return $this->application->make(Request::class);
    }

    /**
     * get user from given auth
     *
     * @return \App\Models\User|null
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function getUser(): ?User
    {
        /** @var User $user */
        $user = $this->getRequest()->user();

        if (! $user->relationLoaded('partners')) {
            $user->load('partners');
        }

        return $user;
    }

    /**
     * @param string $role
     * @return bool
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function isAuthorizeByRole(string $role): bool
    {
        $user = $this->getUser();

        if ($user) {
            return $user->partners->some('pivot.role', $role);
        }

        return false;
    }

    /**
     * @param string $type
     * @return bool
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function isAuthorizeByType(string $type): bool
    {
        $user = $this->getUser();

        if ($user) {
            return $user->partners->some('type', $type);
        }

        return false;
    }
}
