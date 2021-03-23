<?php

namespace App\Supports\Repositories\PartnerRepository;


use App\Models\Partners\Partner;
use App\Models\Partners\Pivot\UserablePivot;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Queries
{
    private Partner $partner;

    private string $role;

    private User $user;

    public function __construct(User $user, Partner $partner, string $role)
    {
        $this->partner = $partner;
        $this->role = $role;
        $this->user = $user;
    }

    public function getDeliveriesQuery(): HasMany
    {
        $query = $this->partner->deliveries();

        $this->resolvePersonalizedQueryByRole($query);

        return $query;
    }

    protected function resolvePersonalizedQueryByRole(HasMany $deliveriesQueryBuilder): void
    {
        switch (true) {
            case $this->role === UserablePivot::ROLE_CS:
                $deliveriesQueryBuilder->whereNull('transporter_id');
                break;
            case $this->role === UserablePivot::ROLE_DRIVER:
                $deliveriesQueryBuilder
                    ->whereHas('transporters', fn(Builder $builder) => $builder
                        ->where('id', $this->user->id));
                break;
            case $this->role === UserablePivot::ROLE_WAREHOUSE:
                break;
        }
    }
}
