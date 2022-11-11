<?php

namespace App\Listeners\Partners;

use App\Actions\Pricing\PricingCalculator;
use App\Actions\Transporter\ShippingCalculator;
use App\Broadcasting\User\PrivateChannel;
use App\Events\Deliveries\Transit as DeliveryTransit;
use App\Events\Deliveries\Dooring as DeliveryDooring;
use App\Events\Partners\Balance\WithdrawalRequested;
use App\Jobs\Partners\Balance\CreateNewBalanceDeliveryHistory;
use App\Jobs\Partners\Balance\CreateNewBalanceHistory;
use App\Jobs\Partners\Balance\CreateNewFailedBalanceHistory;
use App\Models\Deliveries\Delivery;
use App\Models\Notifications\Template;
use App\Models\Packages\Package;
use App\Models\Packages\Price;
use App\Models\Partners\Balance\DeliveryHistory;
use App\Models\Partners\Balance\History;
use App\Models\Partners\Partner;
use App\Models\Partners\Pivot\UserablePivot;
use App\Models\Partners\Prices\Dooring;
use App\Models\Partners\Prices\Transit as PartnerTransitPrice;
use App\Models\Partners\Transporter;
use App\Models\Payments\Withdrawal;
use App\Models\User;
use App\Notifications\Telegram\TelegramMessages\Finance\TransporterBalance;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

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
            case $event instanceof WithdrawalRequested:
                $this
                    ->setWithdrawal($this->event->withdrawal)
                    ->setPartner($this->withdrawal->partner)
                    ->setBalance($this->withdrawal->amount)
                    ->setType(History::TYPE_WITHDRAW)
                    ->setDescription($this->getDescriptionByTypeWithdrawal())
                    ->setAttributes()
                    ->recordHistory();
                break;
            case $event instanceof DeliveryTransit\PackageLoadedByDriver || $event instanceof DeliveryDooring\PackageLoadedByDriver:
                $this
                    ->setDelivery()
                    ->setPackages()
                    ->setPartner($this->delivery->origin_partner);

                /** @var Package $package */
                foreach ($this->packages as $package) {
                    $this->setPackage($package);
                    $variant = '1';
                    # total balance service > record service balance
                    if (!$this->partner->get_fee_transit) {
                        break;
                    }
                    if ($this->countDeliveryTransitOfPackage() > 1) {
                        // $this->saveServiceFee($this->partner->type, $variant, true);
                        /**Set fee transit to be income partner */
                        $income = $this->saveServiceFee($this->partner->type, $variant, true);
                        $this->partner->balance += $income;
                        $this->partner->save();
                    }
                    if ($this->delivery->type === Delivery::TYPE_DOORING) {
                        $this->saveServiceFee($this->partner->type, $variant, true);
                        /**Set fee transit at dooring to be income partner */
                        $income = $this->saveServiceFee($this->partner->type, $variant, true);
                        $this->partner->balance += $income;
                        $this->partner->save();
                    }
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
                        /** Declare for default value */
                        $servicePrice = 0;
                        $balance_handling = 0;
                        $balance_insurance = 0;
                        $balancePickup = 0;
                        $extraFee = 0;
                        $bikeFeeHandling = 0;
                        $feeAdditional = 0;

                        # total balance service > record service balance
                        if ($this->partner->get_fee_service) {
                            $variant = '0';
                            // $this->saveServiceFee($this->partner->type, $variant);
                            $servicePrice = $this->saveServiceFee($this->partner->type, $variant);

                            /** Get fee extra be as commission partners with 0.05*/
                            if ($package->total_weight > 99) {
                                $extraFee = $package->service_price * 0.05;
                                $this
                                    ->setBalance($extraFee)
                                    ->setType(History::TYPE_CHARGE)
                                    ->setDescription(History::DESCRIPTION_ADDITIONAL)
                                    ->setAttributes()
                                    ->recordHistory();
                            }

                            /**Get fee additional */
                            if ($package->total_weight > 100) {
                                $feeAdditional = $package->prices()->where('type', Price::TYPE_SERVICE)->where('description', Price::TYPE_ADDITIONAL)->first()->amount;
                                $this
                                    ->setBalance($feeAdditional)
                                    ->setType(History::TYPE_DEPOSIT)
                                    ->setDescription(History::DESCRIPTION_ADDITIONAL)
                                    ->setAttributes()
                                    ->recordHistory();
                            }
                        }

                        # total balance insurance > record insurance fee
                        if ($this->partner->get_fee_insurance) {
                            $balance_insurance = $package->items()->where('is_insured', true)->get()->sum(function ($item) {
                                return $item->price * PricingCalculator::INSURANCE_PARTNER;
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
                            $balance_handling = 0;
                            $package_prices = $package->prices()->where('type', Price::TYPE_HANDLING)->get();
                            foreach ($package_prices as $price) {
                                $handling_price = $price->amount;
                                $item_qty = $price->item->qty;
                                $balance_handling += ($handling_price * $item_qty);
                            }

                            if ($balance_handling !== 0.0) {
                                $this
                                    ->setBalance($balance_handling)
                                    ->setType(History::TYPE_DEPOSIT)
                                    ->setDescription(History::DESCRIPTION_HANDLING)
                                    ->setAttributes()
                                    ->recordHistory();
                            }
                        }
                        /**Get Fee Pickup */
                        if ($this->partner->get_fee_pickup) {
                            if ($package->type == Package::TYPE_APP) {
                                $balancePickup = $package->prices()->where('type', Price::TYPE_DELIVERY)->where('description', Price::TYPE_PICKUP)->first()->amount;
                                if ($balancePickup !== 0) {
                                    $this
                                        ->setBalance($balancePickup)
                                        ->setType(History::TYPE_DEPOSIT)
                                        ->setDescription(History::DESCRIPTION_PICKUP)
                                        ->setAttributes()
                                        ->recordHistory();
                                }
                            } else {
                                $balancePickup = 0;
                            }
                        }


                        /** Set balance partner*/
                        $newIncome = $servicePrice + $balancePickup + $balance_handling + $balance_insurance + $bikeFeeHandling + $extraFee + $feeAdditional;

                        $balanceExisting = floatval($this->partner->balance);
                        $totalBalance = $balanceExisting + $newIncome;
                        $this->partner->balance = $totalBalance;
                        $this->partner->save();
                    }
                }

                # fee transporter
                if ($this->partner->code !== $this->transporter->partner->code) {
                    $this->setPartner($this->transporter->partner);

                    // except this partner, cant get income MTAK
                    if ($this->partner->code === 'MTM-CGK-00') {
                        foreach ($this->packages as $packages) {
                            switch ($packages->transit_count) {
                                case 1:
                                    $packages->transit_count = 0;
                                    $packages->save();
                                    break;
                                case 2:
                                    $packages->transit_count = 1;
                                    $packages->save();
                                    break;
                                case 3:
                                    $packages->transit_count = 2;
                                    $packages->save();
                                    break;
                                default:
                                    break;
                            }
                        }
                    }

                    if ($this->partner->get_fee_delivery && $this->countDeliveryTransitOfPackage() >= 1) {
                        $package_count = $this->delivery->packages->count();
                        $manifest_weight = 0;
                        foreach ($this->packages as $package) {
                            $manifest_weight += $package->items->sum(function ($item) {
                                return $item->weight_borne_total;
                            });
                        }

                        if ($manifest_weight < 10) {
                            $manifest_weight = 10;
                        }

                        if ($package_count == 1) {
                            $tier = PricingCalculator::getTierType($manifest_weight);
                            /** @var \App\Models\Partners\Prices\Transit $price */
                            $originRegencyId = $this->delivery->origin_regency_id;
                            $destinationDistrictId = $this->delivery->destination_district_id;

                            $price = $this->getTransitPriceByTypeOfSinglePackage($package, $originRegencyId, $destinationDistrictId);

                            if (!$price) {
                                $job = new CreateNewFailedBalanceHistory($this->delivery, $this->partner);
                                $this->dispatchNow($job);
                                $payload = [
                                    'data' => [
                                        'manifest_code' => $this->delivery->code->content,
                                        'manifest_weight' => $manifest_weight,
                                        'package_count' => $package_count,
                                        'partner_code' => $this->partner->code,
                                        'type' => TransporterBalance::MESSAGE_TYPE_DELIVERY,
                                    ]
                                ];
                                try {
                                    Notification::send($payload, new TransporterBalance());
                                } catch (\Exception $e) {
                                    report($e);
                                    Log::error('TransporterBalance-tlg-err', $payload);
                                }
                                break;
                            }

                            $tierPrice = $this->getTransitTierPrice($package_count, $price, $tier);
                        } else {
                            /** @var \App\Models\Partners\Prices\Transit $price */
                            $tier = PricingCalculator::getTierType($manifest_weight);

                            $originRegencyId = $this->delivery->origin_regency_id;
                            $destinationDistrictId = $this->delivery->destination_district_id;

                            $price = $this->getTransitPriceWithMultiplePackages($this->packages, $originRegencyId, $destinationDistrictId);

                            if (!$price || $price->isEmpty()) {
                                $job = new CreateNewFailedBalanceHistory($this->delivery, $this->partner);
                                $this->dispatchNow($job);
                                $payload = [
                                    'data' => [
                                        'manifest_code' => $this->delivery->code->content,
                                        'manifest_weight' => $manifest_weight,
                                        'package_count' => $package_count,
                                        'partner_code' => $this->partner->code,
                                        'type' => TransporterBalance::MESSAGE_TYPE_DELIVERY,
                                    ]
                                ];
                                try {
                                    Notification::send($payload, new TransporterBalance());
                                } catch (\Exception $e) {
                                    report($e);
                                    Log::error('TransporterBalance-tlg-err', $payload);
                                }
                                break;
                            }

                            $tierPrice = $this->getTransitTierPrice($package_count, $price, $tier);
                        }

                        $this->setBalance($manifest_weight * $tierPrice);
                        $income = $manifest_weight * $tierPrice;

                        $this
                            ->setType(DeliveryHistory::TYPE_DEPOSIT)
                            ->setDescription(DeliveryHistory::DESCRIPTION_DELIVERY)
                            ->setAttributes(false)
                            ->recordHistory(false);

                        $this->partner->balance += $income;
                        $this->partner->save();

                        break;
                    }

                    /** @var Package $package */
                    foreach ($this->packages as $package) {
                        $this->setPackage($package);
                        switch ($this->partner->get_fee_delivery) {
                            case $this->countDeliveryTransitOfPackage() === 1:
                                if ($this->partner->get_fee_delivery) {
                                    $balance = ShippingCalculator::getDeliveryFeeByDistance($this->delivery, false);
                                    if ($balance > 0) {
                                        $this
                                            ->setBalance($balance)
                                            ->setType(History::TYPE_DEPOSIT)
                                            ->setDescription(History::DESCRIPTION_DELIVERY)
                                            ->setAttributes()
                                            ->recordHistory();

                                        # charge partner origin
                                        $this->setPartner($this->delivery->origin_partner);
                                        if ($this->partner->get_charge_delivery) {
                                            $this
                                                ->setBalance($balance)
                                                ->setType(History::TYPE_CHARGE)
                                                ->setDescription(History::DESCRIPTION_DELIVERY)
                                                ->setAttributes()
                                                ->recordHistory();
                                        }
                                    }
                                }
                                break;
                        }
                    }
                }
                break;
            case $event instanceof DeliveryDooring\DriverUnloadedPackageInDooringPoint:
                $this
                    ->setDelivery()
                    ->setTransporter()
                    ->setPartner($this->transporter->partner)
                    ->setPackage($event->package);

                if (!$this->partner->get_fee_dooring) {
                    break;
                }

                $weight = $this->package->total_weight;

                $tier = PricingCalculator::getTierType($weight);
                /** @var Dooring $price */
                $price = Dooring::query()
                    ->where('partner_id', $this->partner->id)
                    ->where('origin_regency_id', $this->package->destination_regency_id)
                    ->where('destination_sub_district_id', $this->package->destination_sub_district_id)
                    ->where('type', $tier)
                    ->first();

                if (!$price || is_null($price)) {
                    $job = new CreateNewFailedBalanceHistory($this->delivery, $this->partner, $this->package);
                    $this->dispatchNow($job);

                    $payload = [
                        'data' => [
                            'manifest_code' => $this->delivery->code->content,
                            'package_code' => $this->package->code->content,
                            'origin' => $this->partner->regency->name,
                            'destination' => $this->package->destination_regency->name . ', ' . $this->package->destination_district->name . ', ' . $this->package->destination_sub_district->name,
                            'package_weight' => $weight,
                            'partner_code' => $this->partner->code,
                            'type' => TransporterBalance::MESSAGE_TYPE_PACKAGE,
                        ]
                    ];
                    try {
                        Notification::send($payload, new TransporterBalance());
                    } catch (\Exception $e) {
                        report($e);
                        Log::error('TransporterBalance-tlg-err', $payload);
                    }
                    break;
                }

                /**Insert dooring income to balance partner */
                $existingBalance = $this->partner->balance;
                $income = $weight * $price->value;
                $balance = $existingBalance + $income;

                $this->partner->balance = $balance;
                $this->partner->save();
                $this
                    ->setBalance($weight * $price->value)
                    ->setType(History::TYPE_DEPOSIT)
                    ->setDescription(History::DESCRIPTION_DOORING)
                    ->setAttributes()
                    ->recordHistory();

                // Set Income Delivery
                if ($this->partner->get_fee_delivery) {
                    $weight = $this->package->total_weight;
                    $tier = PricingCalculator::getTierType($weight);
                    $originPartner = $this->delivery->origin_partner()->first();


                    $price = $this->getTransitPriceByTypeOfSinglePackage($this->package, $originPartner->geo_regency_id, $this->package->destination_district_id);

                    if (!$price) {
                        $job = new CreateNewFailedBalanceHistory($this->delivery, $this->partner, $this->package);
                        $this->dispatchNow($job);
                        $payload = [
                            'data' => [
                                'package_code' => $this->package->code->content,
                                'total_weight' => $weight,
                                'partner_code' => $this->partner->code,
                                'type' => TransporterBalance::MESSAGE_TYPE_DELIVERY,
                            ]
                        ];
                        try {
                            Notification::send($payload, new TransporterBalance());
                        } catch (\Exception $e) {
                            report($e);
                            Log::error('TransporterBalance-tlg-err', $payload);
                        }
                        break;
                    }

                    $tierPrice = $this->getTransitTierPrice(1, $price, $tier);

                    $this->setBalance($weight * $tierPrice);
                    $income = $weight * $tierPrice;

                    $this
                        ->setType(DeliveryHistory::TYPE_DEPOSIT)
                        ->setDescription(DeliveryHistory::DESCRIPTION_DELIVERY)
                        ->setAttributes(false)
                        ->recordHistory(false);

                    $this->partner->balance += $income;
                    $this->partner->save();
                }
                break;
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
        if (!is_null($owner->fcm_token)) {
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
    protected function setAttributes($is_package = true): self
    {
        $this->attributes = [
            'partner_id' => $this->partner->id,
            'balance' => $this->balance,
            'type' => $this->type,
            'description' => $this->description
        ];

        if ($this->type === History::TYPE_WITHDRAW) {
            $this->attributes['disbursement_id'] = $this->withdrawal->id;
        } else {
            if ($is_package) {
                $this->attributes['package_id'] = $this->package->id;
            } else {
                $this->attributes['delivery_id'] = $this->delivery->id;
            }
        }
        return $this;
    }

    protected function setWithdrawal(Withdrawal $withdrawal): self
    {
        $this->withdrawal = $withdrawal;
        return $this;
    }

    protected function setDisbursmentId(int $disbursmentId): self
    {
        $this->id = $disbursmentId;
        return $this;
    }

    /**
     * @return string
     */
    protected function getDescriptionByTypeWithdrawal(): string
    {
        switch ($this->withdrawal->status) {
            case Withdrawal::STATUS_REQUESTED:
                return History::DESCRIPTION_WITHDRAW_REQUESTED;
                break;
            case Withdrawal::STATUS_APPROVED:
                return History::DESCRIPTION_WITHDRAW_APPROVED;
                break;
        }
        // END TODO
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
     * @return float
     */
    protected function getServiceFee(string $type): float
    {
        switch ($this->delivery->type) {
            case Delivery::TYPE_TRANSIT:
                if ($this->countDeliveryTransitOfPackage() === 1) {
                    switch ($type) {
                        case Partner::TYPE_BUSINESS:
                            return Delivery::FEE_PERCENTAGE_BUSINESS;
                        case Partner::TYPE_SPACE:
                            return Delivery::FEE_PERCENTAGE_SPACE;
                        case Partner::TYPE_POS:
                            return Delivery::FEE_PERCENTAGE_POS;
                        case Partner::TYPE_HEADSALES:
                            return Delivery::FEE_PERCENTAGE_HEADSALES;
                        case Partner::TYPE_SALES:
                            return Delivery::FEE_PERCENTAGE_SALES;
                    }
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
     * @param bool $is_package
     * @return bool
     */
    protected function noHistory(bool $is_package = true): bool
    {
        $historyQuery = $is_package ? History::query() : DeliveryHistory::query();
        $historyQuery->where('partner_id', $this->partner->id);
        $historyQuery->where('type', $this->type);
        $historyQuery->where('description', $this->description);

        if ($this->type === History::TYPE_WITHDRAW) {
            $historyQuery->where('disbursement_id', $this->withdrawal->id);
        } else {
            if ($is_package) {
                $historyQuery->where('package_id', $this->package->id);
            } else {
                $historyQuery->where('delivery_id', $this->delivery->id);
            }
        }

        return is_null($historyQuery->first());
    }

    /**
     * Insert partner balance history to database.
     * @param bool $is_package
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function recordHistory(bool $is_package = true): void
    {
        if ($this->noHistory($is_package)) {
            if ($is_package) {
                $this->dispatch(new CreateNewBalanceHistory($this->attributes));
            }
            #TODO: job create delivery history
            else {
                $this->dispatch(new CreateNewBalanceDeliveryHistory($this->attributes));
            }
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
    protected function saveServiceFee(string $type, string $variant, bool $isTransit = false)
    {
        $service_price = $this->package->prices->where('type', Price::TYPE_SERVICE)->where('description', Price::TYPE_SERVICE)->first()->amount;
        if ($variant == '0') {
            $discount = 0;
            $check = $this->package->prices->where('type', Price::TYPE_DISCOUNT)->where('description', Price::TYPE_SERVICE)->first();
            if (is_null($check)) {
                $discount = 0;
            } else {
                $discount = $check->amount;
            }
            $balance_service = ($service_price  * $this->getServiceFee($type)) - $discount;
        } else {
            $balance_service = $service_price * $this->getServiceFee($type);
        }

        $this
            ->setBalance($balance_service)
            ->setType(History::TYPE_DEPOSIT)
            ->setDescription($isTransit ? History::DESCRIPTION_TRANSIT : History::DESCRIPTION_SERVICE)
            ->setAttributes()
            ->recordHistory();

        return $balance_service;
    }

    /**
     * Get Transit Tier Price Of Partner Transporter
     */
    protected function getTransitTierPrice($package_count, $price, $tier): float
    {
        if ($package_count > 1) {
            switch ($tier) {
                case 1:
                    return $price->sum('tier_1');
                    break;
                case 2:
                    return $price->sum('tier_2');
                    break;
                case 3:
                    return $price->sum('tier_3');
                    break;
                case 4:
                    return $price->sum('tier_4');
                    break;
                case 5:
                    return $price->sum('tier_5');
                    break;
                case 6:
                    return $price->sum('tier_6');
                    break;
                case 7:
                    return $price->sum('tier_7');
                    break;
                case 8:
                    return $price->sum('tier_8');
                    break;

                default:
                    return 0;
                    break;
            }
        } else {
            switch ($tier) {
                case 1:
                    return $price->tier_1;
                    break;
                case 2:
                    return $price->tier_2;
                    break;
                case 3:
                    return $price->tier_3;
                    break;
                case 4:
                    return $price->tier_4;
                    break;
                case 5:
                    return $price->tier_5;
                    break;
                case 6:
                    return $price->tier_6;
                    break;
                case 7:
                    return $price->tier_7;
                    break;
                case 8:
                    return $price->tier_8;
                    break;

                default:
                    return 0;
                    break;
            }
        }
    }

    /**
     * Get Transit Price By Type MTAK
     * With A Single Packages
     * @return PartnerTransitPrice $price
     */
    protected function getTransitPriceByTypeOfSinglePackage($package, $originRegencyId, $destinationDistrictId)
    {
        $transitCount = $package->transit_count;

        switch ($transitCount) {
            case 1:
                $price = PartnerTransitPrice::query()
                    ->where('origin_regency_id', $originRegencyId)
                    ->where('destination_district_id', $destinationDistrictId)
                    ->where('type', $transitCount)
                    ->first();
                if (is_null($price)) {
                    return 0;
                } else {
                    return $price;
                }
                break;

            case 2:
                $price = PartnerTransitPrice::query()
                    ->where('origin_regency_id', $originRegencyId)
                    ->where('destination_district_id', $destinationDistrictId)
                    ->where('type', $transitCount)
                    ->first();

                if (is_null($price)) {
                    return 0;
                } else {
                    return $price;
                }
                break;

            default:
                $price = PartnerTransitPrice::query()
                    ->where('origin_regency_id', $originRegencyId)
                    ->where('destination_district_id', $destinationDistrictId)
                    ->where('type', $transitCount)
                    ->first();

                if (is_null($price)) {
                    return 0;
                } else {
                    return $price;
                }
                break;
        }
    }

    /**
     * Get Transit Price By Type MTAK
     * With A Multiple Packages
     */
    protected function getTransitPriceWithMultiplePackages($package, $originRegencyId, $destinationDistrictId)
    {
        $transitCount = [];

        foreach ($package as $p) {
            $count = $p['transit_count'];
            array_push($transitCount, $count);
        }

        $price = PartnerTransitPrice::query()
            ->where('origin_regency_id', $originRegencyId)
            ->where('destination_district_id', $destinationDistrictId)
            ->whereIn('type', $transitCount)
            ->get();

        return $price;
    }
}
