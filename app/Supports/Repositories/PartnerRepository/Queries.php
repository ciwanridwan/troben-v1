<?php

namespace App\Supports\Repositories\PartnerRepository;

use App\Models\HistoryReject;
use App\Models\Partners\Balance\History;
use App\Models\Payments\Payment;
use App\Models\User;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use App\Models\Deliveries\Delivery;
use App\Models\Partners\Balance\DeliveryHistory;
use App\Models\Partners\Transporter;
use App\Supports\Repositories\PartnerBalanceReportRepository;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Partners\Pivot\UserablePivot;
use App\Models\Payments\Withdrawal;
use Illuminate\Support\Facades\DB;

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

        $query->orderByDesc('created_at');
        return $query;
    }

    public function getDeliveriesQueryByOwner(): Builder
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

            // $this->resolveDeliveriesQueryByRole($query);
            $query->whereIn('status', [Delivery::STATUS_FINISHED, Delivery::STATUS_EN_ROUTE]);
            $query->whereIn('type', [Delivery::TYPE_DOORING, Delivery::TYPE_TRANSIT, Delivery::TYPE_PICKUP]);
        }

        $query->orderByDesc('created_at');
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
        ])->whereHas('packages');

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
            default:
                $deliveriesQueryBuilder->whereIn('type', [
                    Delivery::TYPE_DOORING,
                    Delivery::TYPE_TRANSIT,
                    Delivery::TYPE_RETURN,
                ]);
                break;
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
                        ->whereIn('packages.status', [Package::STATUS_CANCEL, Package::STATUS_PAID_CANCEL, Package::STATUS_WAITING_FOR_CANCEL_PAYMENT]))

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
                        ]))
                        // ->where('packager_id', $this->user->id))
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

    public function getDashboardIncome($partnerId, $currentDate, $previousDate)
    {
        $q = "select
        income.balance,
        income.total_income current_income,
        coalesce(sum(pbh2.balance), 0) + coalesce(sum(pbdh2.balance), 0) previous_income,
            income.total_income - (coalesce(sum(pbh2.balance), 0) + coalesce(sum(pbdh2.balance), 0)) as increased_income
        from
            (
            select
                p.id partner_id,
                p.balance balance,
                coalesce(sum(pbh.balance), 0) + coalesce(sum(pbdh.balance), 0) as total_income
            from
                partners p
            left join (
                select
                    *
                from
                    partner_balance_histories
                where
                    to_char(created_at, 'YYYY-MM') = '%s'
                    and type = 'deposit') pbh on
                p.id = pbh.partner_id
            left join (
                select
                    *
                from
                    partner_balance_delivery_histories
                where
                    to_char(created_at, 'YYYY-MM') = '%s'
                        and type = 'deposit') pbdh on
                p.id = pbdh.partner_id
            where
                p.id = '%s'
            group by
                p.id
        ) income
        left join (
            select
                *
            from
                partner_balance_histories
            where
                type = 'deposit'
                and to_char(created_at, 'YYYY-MM') = '%s'
                ) pbh2 on
            income.partner_id = pbh2.partner_id
        left join (
            select
                *
            from
                partner_balance_delivery_histories
            where
                type = 'deposit'
                and to_char(created_at, 'YYYY-MM') = '%s'
                ) pbdh2 on
            income.partner_id = pbdh2.partner_id
        group by
            income.partner_id,
            income.balance,
            income.total_income";

        $q = sprintf($q, $currentDate, $currentDate, $partnerId, $previousDate, $previousDate);
        return $q;
    }

    public function getIncomePerDay($partnerId, $currentDate)
    {
        $q = "select
                    sum(pbdh.balance) as amount,
                    to_char(pbdh.created_at, 'yyyy-mm-dd') as date
                from
                    partner_balance_delivery_histories pbdh
                left join (
                    select
                        *
                    from
                        codes
                    where
                        codeable_type = 'App\Models\Deliveries\Delivery') c on
                    pbdh.delivery_id = c.codeable_id
                left join (
                    select
                        delivery_id,
                        sum(p.total_weight) total_weight
                    from
                        deliverables
                    left join packages p on
                        deliverables.deliverable_id = p.id
                    where
                        deliverable_type = 'App\Models\Packages\Package'
                    group by
                        delivery_id
                                ) d on
                    pbdh.delivery_id = d.delivery_id
                where
                    pbdh.partner_id = '%s'
                    and to_char(pbdh.created_at, 'YYYY-MM') = '%s'
                group by
                    to_char(pbdh.created_at, 'yyyy-mm-dd')
                union all
                                select
                    sum(pbh.balance) amount,
                    to_char(pbh.created_at, 'yyyy-mm-dd') created_at
                from
                    partner_balance_histories pbh
                left join (
                    select
                        *
                    from
                        codes
                    where
                        codeable_type = 'App\Models\Packages\Package') c2 on
                    pbh.package_id = c2.codeable_id
                left join packages p2 on
                    c2.codeable_id = p2.id
                where
                    pbh.partner_id = '%s'
                    and pbh.type != 'withdraw'
                    and to_char(pbh.created_at, 'YYYY-MM') = '%s'
                group by
                    to_char(pbh.created_at, 'yyyy-mm-dd')
                order by
                    date desc";

        $q = sprintf($q, $partnerId, $currentDate, $partnerId, $currentDate);
        return $q;
    }

    public function getDisbursmentHistory($partnerId)
    {
        $q = "select
        pbd.created_at,
        pbd.transaction_code,
        pbd.first_balance request_amount,
        case
            when pbd.status = 'requested' then 0
            else pbd.amount
        end total_accepted,
        pbd.status
        from
            partners p
        left join partner_balance_disbursement pbd on
            p.id = pbd.partner_id
        where
        p.id = $partnerId";
        return $q;
    }

    /**
     * Get packages by owner of web dashboard owner
     */
    public function getPackagesQueryByOwner($type, $date): Builder
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

        $packageStatus = [];
        if ($type === 'arrival') {
            $packageStatus = [
                Package::STATUS_WAITING_FOR_ESTIMATING,
                Package::STATUS_ESTIMATING,
                Package::STATUS_WAITING_FOR_PACKING,
                Package::STATUS_PACKING,
            ];
        } else {
            $packageStatus = [
                Package::STATUS_PACKED,
                Package::STATUS_IN_TRANSIT
            ];
        }

        if (!is_null($date)) {
            $month = substr($date, 0, 2);
            $query->whereMonth('created_at', $month);

            $year = substr($date, 3);
            $query->whereYear('created_at', $year);
        }

        $query->whereIn('status', $packageStatus);
        $query->orderByDesc('created_at');

        return $query;
    }

    public function getPreviousPackagesByOwner($type, $date): Builder
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

        $packageStatus = [];
        if ($type === 'arrival') {
            $packageStatus = [
                Package::STATUS_WAITING_FOR_ESTIMATING,
                Package::STATUS_ESTIMATING,
                Package::STATUS_PACKING,
                Package::STATUS_PACKED,
                Package::STATUS_IN_TRANSIT
            ];
        } else {
            $packageStatus = [
                Package::STATUS_IN_TRANSIT
            ];
        }


        $month = substr($date, 0, 2);
        $query->whereMonth('created_at', $month);

        $year = substr($date, 3);
        $query->whereYear('created_at', $year);

        $query->whereIn('status', $packageStatus);
        $query->orderByDesc('created_at');
        return $query;
    }

    public function getTotalItem($packageId)
    {
        $id = implode(",", $packageId);

        $q = "select
                    sum(pi2.qty) total_item
                from
                    packages p
                left join package_items pi2 on
                    p.id = pi2.package_id
                where
                    p.id in ($id)";

        return $q;
    }

    public function getHistoryItemPerday($packageId)
    {
        $id = implode(",", $packageId);

        $q = "select
                    sum(pi2.qty) qty,
                    to_char(pi2.created_at, 'yyyy-mm-dd') date
                from
                    packages p
                left join package_items pi2 on
                        p.id = pi2.package_id
                where
                        p.id in ($id)
                group by date";

        return $q;
    }

    public function getDetailIncomeDashboard($partnerId, $code, $date)
    {
        $pbdhQuery = '';
        $pbhQuery = '';

        if ($code !== "''") {
            $pbdhQuery = "AND c.content ilike '%$code%'";
            $pbhQuery = "AND c2.content ilike '%$code%'";
        }

        $q = "select pbdh.balance as amount, c.content as package_code, pbdh.created_at as date, pbdh.description, pbdh.type,
            d.total_weight as weight
            from partner_balance_delivery_histories pbdh
            left join (select * from codes where codeable_type = 'App\Models\Deliveries\Delivery') c on pbdh.delivery_id = c.codeable_id
            left join (
            	select delivery_id, sum(p.total_weight) total_weight from deliverables
            	left join packages p on deliverables.deliverable_id = p.id
		    	where deliverable_type = 'App\Models\Packages\Package'
            	group by delivery_id
            ) d on pbdh.delivery_id = d.delivery_id
            where pbdh.partner_id = $partnerId
            and to_char(pbdh.created_at, 'YYYY-MM') = '%s'
            %s
            union all
            select pbh.balance, c2.content, pbh.created_at, pbh.description, pbh.type, p2.total_weight from partner_balance_histories pbh
            left join (select * from codes where codeable_type = 'App\Models\Packages\Package') c2 on pbh.package_id = c2.codeable_id
            left join packages p2 on c2.codeable_id = p2.id
            where pbh.partner_id = $partnerId and to_char(pbh.created_at, 'YYYY-MM') = '%s'
            and pbh.type != 'withdraw' %s order by date desc";

        $q = sprintf($q, $date, $pbdhQuery, $date, $pbhQuery);
        return $q;
    }

    public function getDeliveriesTransitByOwner(): Builder
    {
        $listStatus = [
            Delivery::STATUS_ACCEPTED,
            Delivery::STATUS_WAITING_PARTNER_ASSIGN_TRANSPORTER,
            Delivery::STATUS_WAITING_TRANSPORTER
        ];

        $query = Delivery::query();
        $query->where('origin_partner_id', $this->partner->id);
        $query->whereIn('status', $listStatus);
        $this->resolveDeliveriesQueryByRole($query);

        $query->orderByDesc('created_at');

        return $query;
    }

    public function getWithdrawalQuery(): Builder
    {
        $query = Withdrawal::query();
        $query->where('partner_id', $this->partner->id);

        $query->orderByDesc('created_at');
        return $query;
    }

    /**
     * Get income MTAK partner
     */
    public function getIncomeMtak($partnerId)
    {
        $deliveryIncome = DeliveryHistory::with(['partner', 'deliveries.packages', 'deliveries.code'])->where('partner_id', $partnerId)->get();
        $resultsDelivery = $deliveryIncome->map(function ($r) {
            $packages = $r->deliveries->packages()->get();

            $totalWeight = $packages->map(function ($p) {
                $resTotal = $p->items->sum('weight_borne_total');

                return $resTotal;
            })->toArray();

            $totalWeight = array_sum($totalWeight);

            return [
                'package_code' => $r->deliveries->code->content,
                'total_amount' => $r->balance,
                'weight' => $totalWeight,
                'date' => $r->created_at->format('Y-m-d H:i:s'),
                'type' => $r->type,
                'description' => $r->description
            ];
        })->values()->toArray();

        $balanceHistory = History::with(['partner', 'package'])->where('partner_id', $partnerId)->get();

        $resultHistory = $balanceHistory->map(function ($r) {
            $totalWeight = $r->package->items->sum('weight_borne_total');

            return [
                'package_code' => $r->package->code->content,
                'total_amount' => $r->balance,
                'weight' => $totalWeight,
                'date' => $r->created_at->format('Y-m-d H:i:s'),
                'type' => $r->type,
                'description' => $r->description
            ];
        })->values()->toArray();

        $results = array_merge($resultsDelivery, $resultHistory);
        return $results;
    }
}
