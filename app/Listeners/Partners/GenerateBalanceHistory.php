<?php

namespace App\Listeners\Partners;

use App\Actions\Pricing\PricingCalculator;
use App\Actions\Transporter\ShippingCalculator;
use App\Broadcasting\User\PrivateChannel;
use App\Events\Deliveries\Pickup as DeliveryPickup;
use App\Events\Deliveries\Transit as DeliveryTransit;
use App\Events\Partners\Balance\WithdrawalConfirmed;
use App\Events\Partners\Balance\WithdrawalRejected;
use App\Events\Partners\Balance\WithdrawalRequested;
use App\Events\Partners\Balance\WithdrawalSuccess;
use App\Jobs\Partners\Balance\CreateNewBalanceHistory;
use App\Models\Deliveries\Delivery;
use App\Models\Notifications\Template;
use App\Models\Packages\Package;
use App\Models\Packages\Price;
use App\Models\Partners\Balance\History;
use App\Models\Partners\Partner;
use App\Models\Partners\Pivot\UserablePivot;
use App\Models\Partners\Transporter;
use App\Models\Payments\Withdrawal;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Arr;

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
     * History attributes;.
     *
     * @var array $attributes
     */
    protected array $attributes;

    protected Withdrawal $withdrawal;

    protected string $type;
    protected string $description;

    /**
     * Handle event to generate partner's balance.
     *
     * @param object|Delivery $event
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Throwable
     */
    public function handle(object $event)
    {
        $this->event = $event;

        switch (true) {
            case $event instanceof WithdrawalRequested || $event instanceof WithdrawalConfirmed || $event instanceof WithdrawalSuccess || $event instanceof WithdrawalRejected:
                $this
                    ->setWithdrawal($this->event->withdrawal)
                    ->setBalance($this->withdrawal->amount)
                    ->setType(History::TYPE_WITHDRAW)
                    ->setDescription($this->getDescriptionByTypeWithdrawal())
                    ->setAttributes()
                    ->recordHistory();
                break;
            case $event instanceof DeliveryPickup\DriverUnloadedPackageInWarehouse:
                if ($this->event->delivery->transporter) {
                    $this
                        ->setDelivery()
                        ->setPackages()
                        ->setTransporter()
                        ->setPartner($this->transporter->partner);
                    if ($this->partner->get_fee_pickup) $this
                        ->setPackage($this->packages[0])
                        ->setBalance($this->getPickupFee())
                        ->setType(History::TYPE_DEPOSIT)
                        ->setDescription(History::DESCRIPTION_PICKUP)
                        ->setAttributes()
                        ->recordHistory();
                }
                break;
            case $event instanceof DeliveryTransit\PackageLoadedByDriver:
                $this
                    ->setDelivery()
                    ->setPackages()
                    ->setPartner($this->delivery->origin_partner);

                /** @var Package $package */
                foreach ($this->packages as $package) {
                    $this->setPackage($package);

                    # total balance service > record service balance
                    if ($this->partner->get_fee_transit && $this->countDeliveryTransitOfPackage() > 1) $this->saveServiceFee(true);
                }
                break;
            case $event instanceof DeliveryTransit\DriverUnloadedPackageInDestinationWarehouse:
                $this
                    ->setDelivery()
                    ->setPackages()
                    ->setPartner($this->delivery->origin_partner)
                    ->setTransporter();

                # fee MB/MS/MPW
                foreach ($this->packages as $package) {
                    $this->setPackage($package);

                    if ($this->countDeliveryTransitOfPackage() === 1) {
                        # total balance service > record service balance
                        if ($this->partner->get_fee_service) $this->saveServiceFee();
                        # total balance insurance > record insurance fee
                        if ($this->partner->get_fee_insurance) {
                            $balance_insurance = $package->items()->where('is_insured', true)->get()->sum(function ($item) {
                                return $item->price * PricingCalculator::INSURANCE_MUL_PARTNER;
                            });
                            if ($balance_insurance !== 0) {
                                $this
                                    ->setBalance($balance_insurance)
                                    ->setType(History::TYPE_DEPOSIT)
                                    ->setDescription(History::DESCRIPTION_INSURANCE)
                                    ->setAttributes()
                                    ->recordHistory();
                            }
                        }

                        # total balance handling > record handling fee
                        if ($this->partner->get_fee_handling) {
                            $balance_handling = (float)$package->prices()->where('type', Price::TYPE_HANDLING)->sum('amount');
                            if ($balance_handling !== 0.0) {
                                $this
                                    ->setBalance($balance_handling)
                                    ->setType(History::TYPE_DEPOSIT)
                                    ->setDescription(History::DESCRIPTION_HANDLING)
                                    ->setAttributes()
                                    ->recordHistory();
                            }
                        }
                    }
                }

                # fee transporter
                if ($this->partner->code !== $this->transporter->partner->code) {
//                dd($this->partner->code, $this->transporter->partner->code);
                    $this->setPartner($this->transporter->partner);
                    /** @var Package $package */
                    foreach ($this->packages as $package) {
                        $this->setPackage($package);
                        switch ($this->partner->get_fee_delivery) {
//                            case $this->countDeliveryTransitOfPackage() > 1:
//                                $weight = $this->package->items->sum(function ($item) {
//                                    return $item->weight_borne_total;
//                                });
//                                // $partner_price = PricingCalculator::getPartnerPrice($this->partner, $this->delivery->origin_regency_id, $this->delivery->destination_sub_district_id);
//                                // $price = PricingCalculator::getTier($partner_price, $weight);
//                                // TODO: change $price to actual price
//                                $this
//                                    ->setBalance($weight * 1000)
//                                    ->setType(History::TYPE_DEPOSIT)
//                                    ->setDescription(History::DESCRIPTION_DELIVERY)
//                                    ->setAttributes()
//                                    ->recordHistory();
//                                break;
                            case $this->countDeliveryTransitOfPackage() === 1:
                                if ($this->partner->get_fee_delivery) {
                                    $balance = ShippingCalculator::getDeliveryFeeByDistance($this->delivery,false);
                                    if ($balance > 0) {
                                        $this
                                            ->setBalance($balance)
                                            ->setType(History::TYPE_DEPOSIT)
                                            ->setDescription(History::DESCRIPTION_DELIVERY)
                                            ->setAttributes()
                                            ->recordHistory();

                                        # charge partner origin
                                        $this->setPartner($this->delivery->origin_partner);
                                        if ($this->partner->get_charge_delivery) $this
                                            ->setBalance($balance)
                                            ->setType(History::TYPE_CHARGE)
                                            ->setDescription(History::DESCRIPTION_DELIVERY)
                                            ->setAttributes()
                                            ->recordHistory();
                                    }
                                }
                                break;
                        }
                    }
                }
                break;
//            case $event instanceof DeliveryDooring\PackageLoadedByDriver:
//                $this
//                    ->setDelivery()
//                    ->setPackages()
//                    ->setTransporter()
//                    ->setPartner($this->transporter->partner);
//
//                foreach ($this->packages as $package) {
//                    $this->setPackage($package);
//
//                    # total balance service > record service balance
//                    $this->saveServiceFee();
//                }
////                $this->pushNotificationToOwner();
//                break;
//            case $event instanceof DeliveryDooring\DriverUnloadedPackageInDooringPoint:
//                $this
//                    ->setDelivery()
//                    ->setTransporter()
//                    ->setPartner($this->transporter->partner)
//                    ->setPackage($event->package);
//
//                $weight = $this->package->items->sum(function ($item) {
//                    return $item->weight_borne_total;
//                });
//                // $partner_price = PricingCalculator::getPartnerPrice($this->partner, $this->partner->geo_regency_id, $this->package->destination_sub_district_id);
//                // $price = PricingCalculator::getTier($partner_price, $weight);
//                // TODO: change $price to actual price
//                $this
//                    ->setBalance($weight * 500)
//                    ->setType(History::TYPE_DEPOSIT)
//                    ->setDescription(History::DESCRIPTION_DOORING)
//                    ->setAttributes()
//                    ->recordHistory();
////                $this->pushNotificationToOwner();
//                break;
        }
    }

    /**
     * Push notification to owner.
     *
     * @return PrivateChannel|void
     */
    public function pushNotificationToOwner()
    {
        /** @var User $owner */
        $owner = $this->partner->users()->wherePivot('role', UserablePivot::ROLE_OWNER)->first();

        /** @var Template $notification */
        $notification = Template::query()->firstWhere('type', '=', Template::TYPE_PARTNER_BALANCE_UPDATED);
        if (! is_null($owner->fcm_token)) {
            return new PrivateChannel($owner, $notification);
        }
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

    /**
     * Define package instance.
     *
     * @param Package $package
     * @return $this
     */
    protected function setPackage(Package $package): self
    {
        $this->package = $package;
        return $this;
    }

    /**
     * Define balance.
     *
     * @param float $balance
     * @return $this
     */
    protected function setBalance(float $balance): self
    {
        $this->balance = $balance;
        return $this;
    }

    /**
     * Define type.
     *
     * @param string $type
     * @return $this
     */
    protected function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Define Description.
     *
     * @param string $description
     * @return $this
     */
    protected function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Set attributes of history.
     *
     * @return $this
     */
    protected function setAttributes(): self
    {
        $this->attributes = [
            'partner_id' => $this->partner->id,
            'balance' => $this->balance,
            'type' => $this->type,
            'description' => $this->description,
        ];

        $this->attributes = $this->type === History::TYPE_WITHDRAW
            ? Arr::prepend($this->attributes,$this->withdrawal->id,'disbursement_id')
            : Arr::prepend($this->attributes,$this->package->id,'package_id');
        return $this;
    }

    protected function setWithdrawal(Withdrawal $withdrawal): self
    {
        $this->withdrawal = $withdrawal;
        return $this;
    }

    /**
     * @return string
     */
    protected function getDescriptionByTypeWithdrawal(): string
    {
        if ($this->withdrawal->status === Withdrawal::STATUS_CREATED) return History::DESCRIPTION_WITHDRAW_REQUEST;
        if ($this->withdrawal->status === Withdrawal::STATUS_CONFIRMED) return History::DESCRIPTION_WITHDRAW_CONFIRMED;
        if ($this->withdrawal->status === Withdrawal::STATUS_REJECTED) return History::DESCRIPTION_WITHDRAW_REJECT;
        return History::DESCRIPTION_WITHDRAW_SUCCESS;
    }

    /**
     * Get default fee.
     *
     * @return int
     * @throws \Throwable
     */
    protected function getPickupFee(): int
    {
        $generalType = Transporter::getGeneralType($this->transporter->type);
        $maxPrice = Transporter::getAvailableTransporterPrices()[$generalType];

        $priceByDistance = ShippingCalculator::getDeliveryFeeByDistance($this->delivery);

        return $maxPrice >= $priceByDistance ? $priceByDistance : $maxPrice;
    }

    /**
     * Get fee by delivery.
     *
     * @return int
     */
    protected function getServiceFee(): int
    {
        switch ($this->delivery->type) {
            case Delivery::TYPE_TRANSIT:
                if ($this->countDeliveryTransitOfPackage() === 1) {
                    return Delivery::FEE_MAIN;
                } else {
                    return $this->getFeeByAreal();
                }
            case Delivery::TYPE_DOORING:
                return $this->getFeeByAreal();
            default:
                // TODO: throw error or sent notification for handle unpredicted condition
                return 0;
        }
    }

    /**
     * Validate fee by area.
     *
     * @return int
     */
    protected function getFeeByAreal(): int
    {
        return $this->partner->isJabodetabek() ? Delivery::FEE_JABODETABEK : Delivery::FEE_NON_JABODETABEK;
    }

    /**
     * Check history recorded.
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
        if ($this->noHistory()) {
            $this->dispatch(new CreateNewBalanceHistory($this->attributes));
        }
    }

    /**
     * Retrieve "count" result of delivery transit by package.
     *
     * @return int
     */
    protected function countDeliveryTransitOfPackage(): int
    {
        return $this->package->deliveries()->where('type', Delivery::TYPE_TRANSIT)->count();
    }

    /**
     * Save service fee for partner balance.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function saveServiceFee(bool $isTransit = false)
    {
        $balance_service = $this->package->total_weight * $this->getServiceFee();
        $this
            ->setBalance($balance_service)
            ->setType(History::TYPE_DEPOSIT)
            ->setDescription($isTransit ? History::DESCRIPTION_TRANSIT : History::DESCRIPTION_SERVICE)
            ->setAttributes()
            ->recordHistory();
    }
}
