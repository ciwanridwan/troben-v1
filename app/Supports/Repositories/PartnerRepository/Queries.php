<?php

namespace App\Supports\Repositories\PartnerRepository;

use App\Models\User;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Partners\Pivot\UserablePivot;
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

        $this->resolveDeliveriesQueryByRole($query);

        return $query;
    }

    public function getPackagesQuery(): Builder
    {
        $query = Package::query();

        $query->whereHas('deliveries',
            fn (Builder $builder) => $builder->where('partner_id', $this->partner->id));

        $this->resolvePackagesQueryByRole($query);

        return $query;
    }

    protected function resolveDeliveriesQueryByRole(HasMany $deliveriesQueryBuilder): void
    {
        switch (true) {
            case $this->role === UserablePivot::ROLE_CS:
                $deliveriesQueryBuilder->whereNull('transporter_id');
                break;
            case $this->role === UserablePivot::ROLE_DRIVER:
                $deliveriesQueryBuilder
                    ->whereHas('transporter', fn (Builder $builder) => $builder
                        ->whereHas('users', fn (Builder $builder) => $builder
                            ->where('users.id', $this->user->id)
                            ->where('userables.role', UserablePivot::ROLE_DRIVER)));
                break;
        }
    }

    protected function resolvePackagesQueryByRole(Builder $query): void
    {
        switch (true) {
            case $this->role === UserablePivot::ROLE_WAREHOUSE:
                $query->where('status', Package::STATUS_ESTIMATING);
                break;
        }
    }
}
