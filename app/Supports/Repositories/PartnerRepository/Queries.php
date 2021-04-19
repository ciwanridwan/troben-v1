<?php

namespace App\Supports\Repositories\PartnerRepository;

use App\Models\User;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use App\Models\Deliveries\Delivery;
use App\Models\Partners\Transporter;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Partners\Pivot\UserablePivot;

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

    public function getDeliveriesQuery(): Builder
    {
        $query = Delivery::query();

        $query->where(fn (Builder $builder) => $builder
            ->orWhere('partner_id', $this->partner->id)
            ->orWhere('origin_partner_id', $this->partner->id));

        $this->resolveDeliveriesQueryByRole($query);

        return $query;
    }

    public function getPackagesQuery(): Builder
    {
        $query = Package::query();

        $queryPartnerId = fn ($builder) => $builder->where('partner_id', $this->partner->id);

        $query->with([
            'deliveries' => $queryPartnerId,
        ]);

        $query->whereHas('deliveries', $queryPartnerId);

        $this->resolvePackagesQueryByRole($query);

        $query->orderByDesc('updated_at');

        return $query;
    }

    /**
     * get transporter driver.
     *
     * @param Partner|null $customPartner will be used when need other partner rather than scoped partner
     * @return Builder
     */
    public function getTransporterDriverQuery(?Partner $customPartner = null): Builder
    {
        $query = UserablePivot::query();

        $partner = $customPartner ?? $this->partner;

        $query->whereHasMorph(
            'userable',
            Transporter::class,
            fn (Builder $transporterQuery) => $transporterQuery->where('partner_id', $partner->id)
        );

        $query->with('userable', 'user');

        return $query;
    }

    protected function resolveDeliveriesQueryByRole(Builder $deliveriesQueryBuilder): void
    {
        switch (true) {
            case $this->role === UserablePivot::ROLE_CS:
                $deliveriesQueryBuilder->whereNull('userable_id');
                break;
            case $this->role === UserablePivot::ROLE_DRIVER:
                $deliveriesQueryBuilder
                    ->whereHas('assigned_to', fn (Builder $builder) => $builder
                        ->where('user_id', $this->user->id));
                break;
            case $this->role === UserablePivot::ROLE_WAREHOUSE:
                $deliveriesQueryBuilder->whereIn('type', [
                    Delivery::TYPE_DOORING,
                    Delivery::TYPE_TRANSIT,
                    Delivery::TYPE_RETURN,
                ]);
        }
    }

    protected function resolvePackagesQueryByRole(Builder $query): void
    {
        if ($this->user->hasRoles(UserablePivot::ROLE_OWNER)) {
            return;
        }

        switch (true) {
            case $this->role === UserablePivot::ROLE_WAREHOUSE:
                $query->where(fn (Builder $builder) => $builder
                    // package that need estimator
                    ->orWhere(fn (Builder $builder) => $builder
                        ->where('status', Package::STATUS_WAITING_FOR_ESTIMATING)
                        ->whereNull('estimator_id'))
                    // condition that need authorization for estimator
                    ->orWhere(fn (Builder $builder) => $builder
                        ->whereIn('packages.status', [
                            Package::STATUS_ESTIMATING,
                            Package::STATUS_ESTIMATED,
                        ])
                        ->where('estimator_id', $this->user->id))
                    // package that need packager
                    ->orWhere(fn (Builder $builder) => $builder
                        ->where('status', Package::STATUS_WAITING_FOR_PACKING)
                        ->whereNull('packager_id'))
                    // condition that need authorization for packager
                    ->orWhere(fn (Builder $builder) => $builder
                        ->whereIn('packages.status', [
                            Package::STATUS_PACKING,
                            Package::STATUS_PACKED,
                        ])
                        ->where('packager_id', $this->user->id)));
                break;
            case $this->role === UserablePivot::ROLE_CASHIER:
                $query->where(fn (Builder $builder) => $builder->whereIn('packages.status', [
                    Package::STATUS_ESTIMATED,
                    Package::STATUS_REVAMP,
                    Package::STATUS_ACCEPTED,
                    Package::STATUS_CANCEL,
                ])->orWhereIn('payment_status', [
                    Package::PAYMENT_STATUS_PENDING,
                    Package::PAYMENT_STATUS_PAID,
                ]));
                break;
        }
    }
}
