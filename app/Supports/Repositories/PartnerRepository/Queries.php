<?php

namespace App\Supports\Repositories\PartnerRepository;


use App\Models\Partners\Partner;
use App\Models\Partners\Pivot\UserablePivot;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Queries
{
    /**
     * @var \App\Models\Partners\Partner
     */
    private Partner $partner;

    private string $role;

    public function __construct(Partner $partner, string $role)
    {
        $this->partner = $partner;
        $this->role = $role;
    }

    public function getDeliveryQuery(): HasMany
    {
        $query = $this->partner->deliveries();

        $this->resolvePersonalizedQuery($query);

        return $query;
    }

    protected function resolvePersonalizedQuery(HasMany $deliveriesQueryBuilder): void
    {
        switch (true) {
            case $this->role === UserablePivot::ROLE_DRIVER:
                $deliveriesQueryBuilder->where('transporter_id', $this);
                break;
        }
    }
}
