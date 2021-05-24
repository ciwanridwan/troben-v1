<?php

namespace App\Supports\Repositories;

use App\Models\User;
use App\Http\Response;
use App\Exceptions\Error;
use Illuminate\Http\Request;

class PackageRepository
{
    protected ?string $scopedRole = null;

    private \Closure $requestCallback;

    public function __construct(\Closure $requestCallback)
    {
        $this->requestCallback = $requestCallback;
    }

    public function queries(): PackageRepository\Queries
    {
        return new PackageRepository\Queries();
    }

    protected function getUser(): ?User
    {
        /** @var User $user */
        $user = $this->getRequest()->user();

        /** @noinspection PhpParamsInspection */
        /** @noinspection PhpUnhandledExceptionInspection */
        throw_if(! $user instanceof User, Error::class, Response::RC_UNAUTHORIZED);

        if (! $user->relationLoaded('partners')) {
            $user->load('partners');
        }

        return $user;
    }

    private function getRequest(): Request
    {
        $requestCallback = $this->requestCallback;

        return $requestCallback();
    }
}
