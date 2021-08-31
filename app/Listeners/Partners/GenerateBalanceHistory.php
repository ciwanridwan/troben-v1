<?php

namespace App\Listeners\Partners;

use App\Broadcasting\User\PrivateChannel;
use App\Events\Deliveries\Pickup as DeliveryPickup;
use App\Events\Deliveries\Transit as DeliveryTransit;
use App\Jobs\Partners\Balance\CreateNewBalanceHistory;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Package;
use App\Models\Packages\Price;
use App\Models\Partners\Balance\History;
use App\Models\Partners\Partner;
use App\Models\Partners\Pivot\UserablePivot;
use App\Models\Partners\Transporter;
use App\Models\User;
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
     * Package instance.
     *
     * @var Package $package
     */
    protected Package $package;

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
     * History attributes;
     *
     * @var array $attributes
     */
    protected array $attributes;

    protected string $type;
    protected string $description;
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
            case $event instanceof DeliveryPickup\DriverUnloadedPackageInWarehouse:
                $this->setPackages()
                    ->setTransporter()
                    ->setPartner($this->transporter->partner)
                    ->setPackage($this->packages[0])
                    ->setBalance($this->getPickupFee())
                    ->setType(History::TYPE_DEPOSIT)
                    ->setDescription(History::DESCRIPTION_PICKUP)
                    ->setAttributes()
                    ->recordHistory();
                break;
            case $event instanceof DeliveryTransit\PackageLoadedByDriver:
                $this->setDelivery()->setPackages()->setPartner($this->delivery->origin_partner);

                /** @var Package $package */
                foreach ($this->packages as $package) {
                    $this->setPackage($package);
                    $balance_service = $package->total_weight * $this->getDeliveryFee();
                    $this->setBalance($balance_service)
                        ->setType(History::TYPE_DEPOSIT)
                        ->setDescription(History::DESCRIPTION_SERVICE)
                        ->setAttributes()
                        ->recordHistory();

                    $balance_insurance = $this->generateBalances($package->items()->where('is_insured', true)->get(), 'price', 0.0002);
                    if ($balance_insurance !== 0) $this
                        ->setBalance($balance_insurance)
                        ->setType(History::TYPE_DEPOSIT)
                        ->setDescription(History::DESCRIPTION_INSURANCE)
                        ->setAttributes()
                        ->recordHistory();

                    $balance_handling = $this->generateBalances($package->prices()->where('type', Price::TYPE_HANDLING)->get(), 'amount');
                    if ($balance_handling !== 0) $this
                        ->setBalance($balance_handling)
                        ->setType(History::TYPE_DEPOSIT)
                        ->setDescription(History::DESCRIPTION_HANDLING)
                        ->setAttributes()
                        ->recordHistory();
                }
                break;
        }
        $this->pushNotificationToOwner();
    }

    /**
     * Define Delivery.
     *
     * @return $this
     */
    protected function setDelivery(): self
    {
        $this->delivery = $this->event->delivery;
        return $this;
    }

    /**
     * Define transporter by delivery.
     *
     * @return $this
     */
    protected function setTransporter(): self
    {
        $this->transporter = $this->event->delivery->transporter;
        return $this;
    }

    /**
     * Define packages by delivery.
     *
     * @return $this
     */
    protected function setPackages(): self
    {
        $this->packages = $this->event->delivery->packages;
        return $this;
    }

    /**
     * Define partner instance.
     *
     * @param Partner $partner
     * @return $this
     */
    protected function setPartner(Partner $partner): self
    {
        $this->partner = $partner;
        return $this;
    }

    protected function setPackage(Package $package): self
    {
        $this->package = $package;
        return $this;
    }

    protected function setBalance(float $balance): self
    {
        $this->balance = $balance;
        return $this;
    }

    protected function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    protected function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    protected function setAttributes(): self
    {
        $this->attributes = [
            'partner_id' => $this->partner->id,
            'package_id' => $this->package->id,
            'balance' => $this->balance,
            'type' => $this->type,
            'description' => $this->description,
        ];
        return $this;
    }

    /**
     * Get default price
     *
     * @return int
     */
    protected function getPickupFee(): int
    {
        $generalType = Transporter::getGeneralType($this->transporter->type);
        return Transporter::getAvailableTransporterPrices()[$generalType];
    }

    /**
     * @return int
     */
    protected function getDeliveryFee(): int
    {
        switch ($this->delivery->type) {
            case Delivery::TYPE_TRANSIT:
                if ($this->package->deliveries()->where('type', Delivery::TYPE_TRANSIT)->count() === 1) return Delivery::FEE_MAIN;
                else return $this->partner->isJabodetabek() ? Delivery::FEE_JABODETABEK : Delivery::FEE_NON_JABODETABEK;
            case Delivery::TYPE_DOORING:
                return $this->partner->isJabodetabek() ? Delivery::FEE_JABODETABEK : Delivery::FEE_NON_JABODETABEK;
            default:
                // TODO: throw error or sent notification for handle unpredicted condition
                return 0;
        }
    }

    /**
     * Check history recorded
     * @return bool
     */
    protected function noHistory(): bool
    {
        $historyQuery = History::query();
        $historyQuery->where('partner_id', $this->partner->id);
        $historyQuery->where('package_id', $this->package->id);
        $historyQuery->where('type', $this->type);
        $historyQuery->where('description', $this->description);

        return is_null($historyQuery->first());
    }

    /**
     * Insert partner balance history to database.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function recordHistory(): void
    {
        if ($this->noHistory()) $this->dispatch(new CreateNewBalanceHistory($this->attributes));
    }

    /**
     * Generate balance by collection.
     *
     * @param Collection $collection
     * @param string $prop
     * @param null $multiplier
     * @param int $count
     * @return float|int
     */
    protected function generateBalances(Collection $collection, string $prop, $multiplier = null, int $count = 0)
    {
        if (count($collection) === 0) return 0;
        if ($count + 1 === count($collection)) return $collection[$count]->$prop * (is_null($multiplier) ? 1 : $multiplier);
        else return ($collection[$count]->$prop * (is_null($multiplier) ? 1 : $multiplier)) + $this->generateBalances($collection, $prop, $multiplier, $count + 1);
    }

    /**
     * Push notification to owner.
     *
     * @return PrivateChannel|void
     */
    protected function pushNotificationToOwner()
    {
        /** @var User $owner */
        $owner = $this->partner->users()->wherePivot('role',UserablePivot::ROLE_OWNER)->first();
        if (!is_null($owner->fcm_token)) return new PrivateChannel($owner, 'Saldo bertambah!', 'Ayo cek saldomu.');
    }
}
