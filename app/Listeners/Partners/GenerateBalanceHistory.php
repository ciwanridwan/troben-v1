<?php

namespace App\Listeners\Partners;

use App\Events\Deliveries\Pickup;
use App\Jobs\Partners\Balance\CreateNewBalanceHistory;
use App\Models\Deliveries\Delivery;
use App\Models\Partners\Balance\History;
use App\Models\Partners\Partner;
use App\Models\Partners\Transporter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\DispatchesJobs;

class GenerateBalanceHistory
{
    use DispatchesJobs;

    /**
     * Handle event.
     *
     * @var Object|Delivery $event
     */
    protected Object $event;

    /**
     * Transporter instance.
     *
     * @var Transporter $transporter
     */
    protected Transporter $transporter;

    /**
     * Delivery instance.
     *
     * @var Delivery $delivery
     */
    protected Delivery $delivery;

    /**
     * Collection of packages.
     *
     * @var Collection $packages
     */
    protected Collection $packages;

    /**
     * Partner instance.
     * This could be origin partner, destination partner or transporter partner.
     *
     * @var Partner $partner
     */
    protected Partner $partner;

    /**
     * Balance for partner.
     *
     * @var float $balance
     */
    protected float $balance;

    /**
     * Handle event to generate partner's balance.
     *
     * @param object|Delivery $event
     * @throws \Illuminate\Validation\ValidationException
     */
    public function handle(object $event)
    {
        $this->event = $event;

        switch (true) {
            case $event instanceof Pickup\DriverUnloadedPackageInWarehouse:
                $this->setTransporter();
                $this->setPackages();
                $this->setPartner($this->transporter->partner);

                if ($this->noHistory()) {
                    $inputs = [
                        'partner_id' => $this->partner->id,
                        'package_id' => $this->packages[0]->id,
                        'balance' => $this->getDefaultPrice(),
                        'type' => History::TYPE_DEPOSIT,
                        'description' => History::DESCRIPTION_PICKUP,
                    ];

                    $job = new CreateNewBalanceHistory($inputs);
                    $this->dispatch($job);
                }
                break;
        }
    }

    /**
     * Define delivery.
     */
    protected function setDelivery(): void
    {
        $this->delivery = $this->event->delivery;
    }

    /**
     * Define transporter by delivery.
     */
    protected function setTransporter(): void
    {
        $this->transporter = $this->event->delivery->transporter;
    }

    /**
     * Define packages by delivery.
     */
    protected function setPackages(): void
    {
        $this->packages = $this->event->delivery->packages;
    }

    /**
     * Define partner instance.
     *
     * @param Partner $partner
     */
    protected function setPartner(Partner $partner): void
    {
        $this->partner = $partner;
    }

    /**
     * Get default price
     *
     * @return int
     */
    protected function getDefaultPrice(): int
    {
        $generalType = Transporter::getGeneralType($this->transporter->type);
        return Transporter::getAvailableTransporterPrices()[$generalType];
    }

    /**
     * Check history recorded
     * @return bool
     */
    protected function noHistory(): bool
    {
        $historyQuery = History::query();
        $historyQuery->where('package_id', $this->packages[0]->id);
        $historyQuery->where('type', History::TYPE_DEPOSIT);
        $historyQuery->where('description', History::DESCRIPTION_PICKUP);

        return is_null($historyQuery->first());
    }
}
