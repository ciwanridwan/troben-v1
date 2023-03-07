<?php

namespace App\Supports\Repositories\PartnerRepository;

use App\Models\HistoryReject;
use App\Models\Partners\Balance\History;
use App\Models\Payments\Payment;
use App\Models\User;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use App\Models\Deliveries\Delivery;
use App\Models\Partners\Transporter;
use App\Supports\Repositories\PartnerBalanceReportRepository;
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

        if ($this->partner->type === Partner::TYPE_TRANSPORTER) {
            $userables = $this->user->transporters;
            $ids = [];
            foreach ($userables as $userable) {
                $ids[] = $userable->pivot->id;
            }
            $query->whereIn('userable_id', $ids);
        } else {
            $query->where(fn (Builder $builder) => $builder
                ->orWhere('partner_id', $this->partner->id)
                ->orWhere('origin_partner_id', $this->partner->id));

            $this->resolveDeliveriesQueryByRole($query);
        }

        return $query;
    }

    public function getDeliveriesRejectCourierQuery(): Builder
    {
        $query = HistoryReject::query();

        $query->where(fn (Builder $builder) => $builder
            ->orWhere('user_id', $this->user->id));

        return $query;
    }

    public function getHistoryRejectedQuery(): Builder
    {
        $query = HistoryReject::query();

        $query->where(fn (Builder $builder) => $builder
            ->orWhere('partner_id', $this->partner->id));

        return $query;
    }

    public function getHistoryRejectedKurirQuery(): Builder
    {
        $query = HistoryReject::query();

        $query->where(fn (Builder $builder) => $builder
            ->orWhere('userable_id', $this->user->id));

        return $query;
    }

    public function getDeliveriesByUserableQuery(): Builder
    {
        $query = Delivery::query();

        $transporters = [];
        foreach ($this->partner->users()->where('role', UserablePivot::ROLE_DRIVER)->get() as $driver) {
            if ($driver->transporters) {
                foreach ($driver->transporters as $transporter) {
                    $transporters[] = $transporter->pivot->id;
                }
            }
        }

        $query->whereIn('userable_id', array_merge(
            [$this->partner->users()->where('role', UserablePivot::ROLE_OWNER)->first()->pivot->id],
            $transporters
        ));
        $query->with([
            'packages',
            'origin_partner',
            'partner',
            'transporter',
            'partner_performance'
        ]);

        return $query;
    }

    public function getCancelQuery(string $type): Builder
    {
        $query = Package::query();

        $queryPartnerId = fn ($builder) => $builder->where('partner_id', $this->partner->id);

        $query->with([
            'deliveries' => $queryPartnerId,
            'canceled' => function ($q) use ($type) {
                $q->where('type', $type);
            },
        ]);

        $query->whereHas('canceled', function ($q) use ($type) {
            $q->where('type', $type);
        });
        $query->whereHas('deliveries', $queryPartnerId);


        $this->resolvePackagesQueryByRole($query);

        $query->orderByDesc('created_at');

        return $query;
    }

    public function getCancelResi(): Builder
    {
        $query = Package::query();
        $queryPartnerId = fn ($builder) => $builder->where('partner_id', $this->partner->id);

        $query->with([
            'deliveries' => $queryPartnerId,
            'canceled'
        ]);
        $query->whereHas('canceled');
        $query->whereHas('deliveries', $queryPartnerId);


        $this->resolvePackagesQueryByRole($query);

        $query->orderByDesc('created_at');

        return $query;
    }

    public function getPackagesQuery(): Builder
    {
        $query = Package::query();

        $queryPartnerId = fn ($builder) => $builder->where('partner_id', $this->partner->id);

        $query->with([
            'deliveries' => $queryPartnerId,
            'deliveries',
            'deliveries.origin_partner',
            'deliveries.partner',
            'deliveryRoutes'
        ]);

        $query->whereHas('deliveries', $queryPartnerId);

        $this->resolvePackagesQueryByRole($query);

        $query->orderByDesc('created_at');

        return $query;
    }

    public function getPaymentQuery(): Builder
    {
        $query = Payment::query();

        $queryPartnerId = fn ($builder) => $builder->where('partner_id', $this->partner->id);

        $query->whereHasMorph('payable', Delivery::class, $queryPartnerId);

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

    /**
     * @return Builder
     */
    public function getPartnerBalanceHistoryQuery(): Builder
    {
        $query = History::query();

        $query->where('partner_id', $this->partner->id);

        return $query;
    }

    /**
     * @return Builder
     * @throws \Illuminate\Validation\ValidationException
     */
    public function getPartnerBalanceReportQuery(): Builder
    {
        $repository = new PartnerBalanceReportRepository([
            'partner_id' => $this->partner->id,
        ]);

        return $repository->getQuery();
    }

    protected function resolveDeliveriesQueryByRole(Builder $deliveriesQueryBuilder): void
    {
        switch (true) {
            case $this->role === UserablePivot::ROLE_CS:
                // $deliveriesQueryBuilder->whereNull('userable_id');
                break;
            case $this->role === UserablePivot::ROLE_DRIVER:
                $deliveriesQueryBuilder
                    ->whereHas('assigned_to', fn (Builder $builder) => $builder
                        ->where('user_id', $this->user->id));

                $userables = $this->user->transporters;
                $ids = [];
                foreach ($userables as $userable) {
                    $ids[] = $userable->pivot->id;
                }
                $deliveriesQueryBuilder->orWhereIn('userable_id', $ids);
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

                    ->orWhere(fn (Builder $builder) => $builder
                        ->whereIn('packages.status', [Package::STATUS_CANCEL,Package::STATUS_PAID_CANCEL,Package::STATUS_WAITING_FOR_CANCEL_PAYMENT]))

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
                        ->where('packager_id', $this->user->id))
                    // condition after driver unloaded the package
                    ->orWhere(fn (Builder $builder) => $builder
                        ->where(
                            'status',
                            Package::STATUS_IN_TRANSIT
                        )));
                break;
            case $this->role === UserablePivot::ROLE_CASHIER:
                $query->where(fn (Builder $builder) => $builder->whereIn('packages.status', [
                    Package::STATUS_WAITING_FOR_PACKING,
                    Package::STATUS_ESTIMATED,
                    Package::STATUS_WAITING_FOR_APPROVAL,
                    Package::STATUS_WAITING_FOR_PAYMENT,
                    Package::STATUS_REVAMP,
                    Package::STATUS_PACKING,
                    Package::STATUS_PACKED,
                    Package::STATUS_ACCEPTED,
                    Package::STATUS_WITH_COURIER,
                    Package::STATUS_CANCEL,
                    Package::STATUS_MANIFESTED,
                    Package::STATUS_IN_TRANSIT,
                    Package::STATUS_DELIVERED,
                ]));
                break;
        }
    }
}
