<?php

namespace App\Supports\Repositories;

use App\Exceptions\Error;
use App\Http\Response;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Partners\Partner;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Arr;

class PartnerRepository
{
    private Application $application;

    protected ?string $role = null;

    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    public function scopeRole(string $role)
    {
        $this->role = $role;
    }

    /**
     * get scoped role or will return first of available roles
     *
     * @return string|null
     */
    public function getScopedRole(): ?string
    {
        if (! $this->role) {
            return Arr::first($this->getRoles());
        }

        return $this->role;
    }

    private function getRequest(): Request
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return $this->application->make(Request::class);
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

    protected function getPartner(): Partner
    {
        $partners = $this->getUser()->partners;

        return $partners->first();
    }

    protected function getRoles(): array
    {
        $partners = $this->getUser()->partners;

        /** @noinspection PhpUndefinedFieldInspection */
        return $partners->pluck('pivot')->map->role->toArray();
    }

    public function isAuthorizeByRoles($roles): bool
    {
        $user = $this->getUser();

        if ($user) {
            $roles = Arr::wrap($roles);

            return $user->partners->some(fn(Partner $partner) => in_array($partner->pivot->role, $roles));
        }

        return false;
    }

    public function isAuthorizeByTypes($types): bool
    {
        $user = $this->getUser();

        if ($user) {
            $types = Arr::wrap($types);

            return $user->partners->some(fn(Partner $partner) => in_array($partner->type, $types));
        }

        return false;
    }

    public function queries(): PartnerRepository\Queries
    {
        return new PartnerRepository\Queries($this->getUser(), $this->getPartner(), $this->role);
    }
}
