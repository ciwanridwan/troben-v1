<?php

namespace App\Supports\Repositories;

use App\Models\User;
use App\Http\Response;
use App\Exceptions\Error;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Models\Partners\Partner;
use App\Models\Partners\Pivot\UserablePivot;

class PartnerRepository
{
    protected ?string $scopedRole = null;

    private \Closure $requestCallback;

    public function __construct(\Closure $requestCallback)
    {
        $this->requestCallback = $requestCallback;
    }

    public function setScopeRole(string $role)
    {
        $this->scopedRole = $role;
    }

    /**
     * get scoped role or will return first of available roles.
     *
     * @return string|null
     */
    public function getScopedRole(): ?string
    {
        if (! $this->scopedRole) {
            return Arr::first($this->getRoles());
        }

        return $this->scopedRole;
    }

    public function getPartner(): Partner
    {
        $partners = $this->getUser()->partners;

        return $partners->first();
    }

    public function getRoles(): array
    {
        $partners = $this->getUser()->partners;

        /** @noinspection PhpUndefinedFieldInspection */
        return $partners->pluck('pivot')->map->role->toArray();
    }

    public function isAuthorizeByRoles($roles): bool
    {
        $user = $this->getUser();

        $resolvedAuthorized = false;

        if ($user) {
            $roles = Arr::wrap($roles);

            $resolvedAuthorized = $user->partners->some(fn (Partner $partner) => in_array($partner->pivot->role, $roles));
        }

        // owner has the right to access all resource
        if (! $resolvedAuthorized && $user->partners->some(fn (Partner $partner) => $partner->pivot->role == UserablePivot::ROLE_OWNER)) {
            $resolvedAuthorized = true;
        }

        return $resolvedAuthorized;
    }

    public function isAuthorizeByTypes($types): bool
    {
        $user = $this->getUser();

        if ($user) {
            $types = Arr::wrap($types);

            return $user->partners->some(fn (Partner $partner) => in_array($partner->type, $types));
        }

        return false;
    }

    public function queries(): PartnerRepository\Queries
    {
        if (! $this->scopedRole) {
            throw new \LogicException('no scoped role provided. please use middleware [partner.scope.role] first or specified scope in the process.');
        }

        return new PartnerRepository\Queries($this->getUser(), $this->getPartner(), $this->scopedRole);
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
