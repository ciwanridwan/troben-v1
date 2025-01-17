<?php

namespace App\Listeners\Partners;

use App\Actions\Deliveries\Route;
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
    protected string $serviceType;
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
                        $bikeServiceFee = 0;
                        $discountServiceFee = 0;
                        $discountPickupFee = 0;
                        // $feeAdditional = 0;

                        # total balance service > record service balance
                        if ($this->partner->get_fee_service) {
                            $variant = '0';
                            if (!is_null($package->motoBikes)) {
                                // add condition for exception on java island depend on partner income
                                $originProvince = $package->origin_regency->province;
                                $destinationProvince = $package->destination_regency->province;

                                $itemBikes =  [
                                    'origin_province_id' => $originProvince->id,
                                    'destination_province_id' => $destinationProvince->id,
                                    'cc' => $package->motoBikes->cc
                                ];

                                $servicePrice = $this->saveServiceFee($this->partner->type, $variant, false, $itemBikes);
                            } else {
                                $servicePrice = $this->saveServiceFee($this->partner->type, $variant);
                            }

                            $discountService = $package->prices()->where('type', Price::TYPE_DISCOUNT)->where('description', Price::TYPE_SERVICE)->first();

                            // set discount fee service
                            if (!is_null($discountService)) {
                                $discountServiceFee = $discountService->amount;
                                $this
                                    ->setBalance($discountServiceFee)
                                    ->setType(History::TYPE_DISCOUNT)
                                    ->setDescription(History::DESCRIPTION_SERVICE)
                                    ->setAttributes()
                                    ->recordHistory();
                            }

                            $serviceFromPackagePrices = $package->prices()->where('type', Price::TYPE_SERVICE)->where('description', '!=', Price::TYPE_ADDITIONAL)->first();
                            $serviceFee = $serviceFromPackagePrices ? $serviceFromPackagePrices->amount : 0;
                            if (!is_null($discountService)) {
                                $serviceFee -= $discountService->amount;
                            }

                            # disable
                            /** Get fee extra be as commission partners with 0.05*/
                            // $extraFee = $this->getIncomeChargePartner($package->total_weight, $serviceFee);
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
                                //$item_qty = $price->item->qty;
                                //remove multiplier qty, as handling calculate already all qty
                                $balance_handling += $handling_price;
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
                                $discountPickup = $package->prices()->where('type', Price::TYPE_DISCOUNT)->where('description', Price::TYPE_PICKUP)->first();

                                // set fee discount pickup
                                if (is_null($package->promos)) {
                                    if (!is_null($discountPickup)) {
                                        $discountPickupFee = $discountPickup->amount;
                                        $this
                                            ->setBalance($discountPickupFee)
                                            ->setType(History::TYPE_DISCOUNT)
                                            ->setDescription(History::DESCRIPTION_PICKUP)
                                            ->setAttributes()
                                            ->recordHistory();
                                    }
                                }

                                $this
                                    ->setBalance($balancePickup)
                                    ->setType(History::TYPE_DEPOSIT)
                                    ->setDescription(History::DESCRIPTION_PICKUP)
                                    ->setAttributes()
                                    ->recordHistory();
                            } else {
                                $balancePickup = 0;
                            }
                        }

                        switch (true) {
                            case $discountServiceFee !== 0:
                                $discount = $discountServiceFee;
                                break;
                            case $discountPickupFee !== 0:
                                $discount = $discountPickupFee;
                                break;
                            default:
                                $discount = 0;
                                break;
                        }

                        /** Set balance partner*/
                        $newIncome = $servicePrice + $balancePickup + $balance_handling + $balance_insurance + $extraFee - $discount;

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

                        $manifest_weight = $this->checkMinimalChargeWeight($this->partner->code, $manifest_weight);


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
                                    //Notification::send($payload, new TransporterBalance());
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
                                    //Notification::send($payload, new TransporterBalance());
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
                                        // $this->setPartner($this->delivery->origin_partner);
                                        // if ($this->partner->get_charge_delivery) {
                                        //     $this
                                        //         ->setBalance($balance)
                                        //         ->setType(History::TYPE_CHARGE)
                                        //         ->setDescription(History::DESCRIPTION_DELIVERY)
                                        //         ->setAttributes()
                                        //         ->recordHistory();
                                        // }
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

                // initialize some income
                $servicePrice = 0;
                $balance_handling = 0;
                $balance_insurance = 0;
                $balancePickup = 0;
                $extraFee = 0;
                $bikeServiceFee = 0;
                $discountServiceFee = 0;
                $discountPickupFee = 0;
                // $feeAdditional = 0;

                // check if package direct dooring doesnt transit
                // and partner should get income
                $routes = Route::getWarehousePartner($this->partner->code, $this->package);
                $isDirectDooring = Route::checkDirectDooring($this->partner, $routes);
                
                // add validation to double check if valid direct dooring or not
                $firstManifest = $this->package->deliveries()->latest()->first();
                $lastManifest = $this->package->deliveries->last();

                if ($firstManifest->partner_id !== $this->partner->id && $firstManifest->partner_id !== $lastManifest->origin_partner_id) {
                    $isDirectDooring = false;
                }

                if ($isDirectDooring) {
                    # total balance service > record service balance
                    if ($this->partner->get_fee_service) {
                        $variant = '0';
                        if (!is_null($this->package->motoBikes)) {
                            // add condition for exception on java island depend on partner income
                            $originProvince = $this->package->origin_regency->province;
                            $destinationProvince = $this->package->destination_regency->province;

                            $itemBikes =  [
                                'origin_province_id' => $originProvince->id,
                                'destination_province_id' => $destinationProvince->id,
                                'cc' => $this->package->motoBikes->cc
                            ];

                            $servicePrice = $this->saveServiceFee($this->partner->type, $variant, false, $itemBikes);
                        } else {
                            $servicePrice = $this->saveServiceFee($this->partner->type, $variant);
                        }

                        $discountService = $this->package->prices()->where('type', Price::TYPE_DISCOUNT)->where('description', Price::TYPE_SERVICE)->first();

                        // set discount fee service
                        if (!is_null($discountService)) {
                            $discountServiceFee = $discountService->amount;
                            $this
                                ->setBalance($discountServiceFee)
                                ->setType(History::TYPE_DISCOUNT)
                                ->setDescription(History::DESCRIPTION_SERVICE)
                                ->setAttributes()
                                ->recordHistory();
                        }

                        /** Get fee extra for commission partners*/
                        /** Get fee extra be as commission partners with 0.05*/
                        $serviceFromPackagePrices = $this->package->prices()->where('type', Price::TYPE_SERVICE)->where('description', '!=', Price::TYPE_ADDITIONAL)->first();
                        $serviceFee = $serviceFromPackagePrices ? $serviceFromPackagePrices->amount : 0;
                        if (!is_null($discountService)) {
                            $serviceFee -= $discountService->amount;
                        }
                        // $extraFee = $this->getIncomeChargePartner($this->package->total_weight, $serviceFee);
                    }

                    # total balance insurance > record insurance fee
                    if ($this->partner->get_fee_insurance) {
                        $balance_insurance = $this->package->items()->where('is_insured', true)->get()->sum(function ($item) {
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
                        $package_prices = $this->package->prices()->where('type', Price::TYPE_HANDLING)->get();
                        foreach ($package_prices as $price) {
                            $handling_price = $price->amount;
                            //$item_qty = $price->item->qty;
                            //remove multiplier qty, as handling calculate already all qty
                            $balance_handling += $handling_price;
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
                        if ($this->package->type == Package::TYPE_APP) {
                            $balancePickup = $this->package->prices()->where('type', Price::TYPE_DELIVERY)->where('description', Price::TYPE_PICKUP)->first()->amount;
                            $discountPickup = $this->package->prices()->where('type', Price::TYPE_DISCOUNT)->where('description', Price::TYPE_PICKUP)->first();

                            // set fee discount pickup
                            if (is_null($this->package->promos)) {
                                if (!is_null($discountPickup)) {
                                    $discountPickupFee = $discountPickup->amount;
                                    $this
                                        ->setBalance($discountPickupFee)
                                        ->setType(History::TYPE_DISCOUNT)
                                        ->setDescription(History::DESCRIPTION_PICKUP)
                                        ->setAttributes()
                                        ->recordHistory();
                                }
                            }

                            $this
                                ->setBalance($balancePickup)
                                ->setType(History::TYPE_DEPOSIT)
                                ->setDescription(History::DESCRIPTION_PICKUP)
                                ->setAttributes()
                                ->recordHistory();
                        } else {
                            $balancePickup = 0;
                        }
                    }

                    switch (true) {
                        case $discountServiceFee !== 0:
                            $discount = $discountServiceFee;
                            break;
                        case $discountPickupFee !== 0:
                            $discount = $discountPickupFee;
                            break;
                        default:
                            $discount = 0;
                            break;
                    }

                    /** Set balance partner*/
                    $newIncome = $servicePrice + $balancePickup + $balance_handling + $balance_insurance + $extraFee - $discount;

                    $balanceExisting = floatval($this->partner->balance);
                    $totalBalance = $balanceExisting + $newIncome;
                    $this->partner->balance = $totalBalance;
                    $this->partner->save();
                }

                if (!is_null($this->package->motoBikes)) {
                    $bikeServiceFee = PricingCalculator::getIncomeBikeDooringPartner($this->package->motoBikes->cc);
                    $this->partner->balance += $bikeServiceFee;
                    $this->partner->save();

                    $this
                        ->setBalance($bikeServiceFee)
                        ->setType(History::TYPE_DEPOSIT)
                        ->setDescription(History::DESCRIPTION_DOORING)
                        ->setServiceType(History::DESCRIPTION_SERVICE_BIKE)
                        ->setAttributes()
                        ->recordHistory();
                    break;
                }

                # set weight without minimal
                $weight = $this->package->items->sum('weight_borne_total');

                # set weight minimal charge weight
                // $weight = $this->package->total_weight;

                $tier = PricingCalculator::getTierType($weight);
                /** @var Dooring $price */
                $price = Dooring::query()
                    ->where('partner_id', $this->partner->id)
                    ->where('origin_regency_id', $this->package->origin_regency_id)
                    ->where('destination_sub_district_id', $this->package->destination_sub_district_id)
                    ->first();

                if (!$this->partner->get_fee_dooring || !$price || is_null($price)) {
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
                        //Notification::send($payload, new TransporterBalance());
                    } catch (\Exception $e) {
                        report($e);
                        Log::error('TransporterBalance-tlg-err', $payload);
                    }
                } else {
                    $tierPrice = $this->getTierTypeDooring($tier, $price);
                    $existingBalance = $this->partner->balance;
                    $income = $weight * $tierPrice;
                    $balance = $existingBalance + $income;

                    $this->partner->balance = $balance;
                    $this->partner->save();
                    $this
                        ->setBalance($income)
                        ->setType(History::TYPE_DEPOSIT)
                        ->setDescription(History::DESCRIPTION_DOORING)
                        ->setAttributes()
                        ->recordHistory();
                }

                # Set Income Delivery
                // if ($this->partner->get_fee_delivery) {
                //     if ($this->delivery->packages()->count() > 1) {
                //         $packages = $this->delivery->packages()->get();
                //         $totalWeightBorne = $packages->map(function ($r) {
                //             return $r->items->sum('weight_borne_total');
                //         })->toArray();

                //         $totalWeight = array_sum($totalWeightBorne);
                //     } else {
                //         $totalWeight = $this->package->items->sum('weight_borne_total');
                //     }

                //     $totalWeight = $this->checkMinimalChargeWeight($this->partner->code, $totalWeight);
                //     $tier = PricingCalculator::getTierType($totalWeight);
                //     $originPartner = $this->delivery->origin_partner()->first();

                //     $price = $this->getTransitPriceByTypeOfSinglePackage($this->package, $originPartner->geo_regency_id, $this->package->destination_district_id);
                //     if (!$price) {
                //         $job = new CreateNewFailedBalanceHistory($this->delivery, $this->partner, $this->package);
                //         $this->dispatchNow($job);

                //          = $this->package->deliveries()->latest();
                // _weight = 0;
                //         try {
                //             $this->package->items->each(function ($item) use (&$manifest_weight) {
                //                 $manifest_weight += $item->weight_borne_total;
                //             });
                //         } catch (\Exception $e) {
                //             report($e);
                //         }
                //         $payload = [
                //             'data' => [
                //                 'manifest_weight' => $manifest_weight,
                //                 'manifest_code' => $this->delivery->code->content,
                //                 'package_count' => 1, // only first item
                //                 'package_code' => $this->package->code->content,
                //                 'total_weight' => $totalWeight,
                //                 'partner_code' => $this->partner->code,
                //                 'type' => TransporterBalance::MESSAGE_TYPE_DELIVERY,
                //             ]
                //         ];
                //         try {
                //             //Notification::send($payload, new TransporterBalance());
                //         } catch (\Exception $e) {
                //             report($e);
                //             Log::error('TransporterBalance-tlg-err', $payload);
                //         }
                //         break;
                //     }

                //     $tierPrice = $this->getTransitTierPrice(1, $price, $tier);

                //     $this->setBalance($totalWeight * $tierPrice);
                //     $income = $totalWeight * $tierPrice;

                //     $this
                //         ->setType(DeliveryHistory::TYPE_DEPOSIT)
                //         ->setDescription(DeliveryHistory::DESCRIPTION_DELIVERY)
                //         ->setAttributes(false)
                //         ->recordHistory(false);

                //     $this->partner->balance += $income;
                //     $this->partner->save();
                // }
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
     * Define service type.
     *
     * @param string $serviceType
     * @return $this
     */
    protected function setServiceType(string $serviceType): self
    {
        $this->serviceType = $serviceType;
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
            'description' => $this->description,
            'services' => $this->serviceType ?? History::DESCRIPTION_SERVICE_REGULAR
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
            else { // recordHistory
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
    protected function saveServiceFee(string $type, string $variant, bool $isTransit = false, $itemBikes = null)
    {
        $service_price = $this->package->prices->where('type', Price::TYPE_SERVICE)->where('description', Price::TYPE_SERVICE)->first();
        if (is_null($service_price)) {
            $this->servicePriceCubic($type, $variant, $isTransit);
        } elseif (!is_null($itemBikes)) {
            $bikeServiceFee = PricingCalculator::getIncomeBikePartner($itemBikes);
            $this
                ->setBalance($bikeServiceFee)
                ->setType(History::TYPE_DEPOSIT)
                ->setDescription(History::DESCRIPTION_SERVICE)
                ->setServiceType(History::DESCRIPTION_SERVICE_BIKE)
                ->setAttributes()
                ->recordHistory();

            return $bikeServiceFee;
        } else {
            if ($variant == '0') {
                $balance_service = $service_price->amount  * $this->getServiceFee($type);
            } else {
                $balance_service = $this->package->total_weight * $this->getServiceFee($type);
            }

            $this
                ->setBalance($balance_service)
                ->setType(History::TYPE_DEPOSIT)
                ->setDescription($isTransit ? History::DESCRIPTION_TRANSIT : History::DESCRIPTION_SERVICE)
                ->setAttributes()
                ->recordHistory();

            return $balance_service;
        }
    }

    protected function servicePriceCubic(string $type, string $variant, $isTransit)
    {
        // $incomeCubic = 0.2;
        // $service_price = $this->package->prices->where('type', Price::TYPE_SERVICE)->where('description', Price::DESCRIPTION_TYPE_CUBIC)->first();

        // if ($variant == '0') {
        //     $balance_service = $service_price->amount  * $incomeCubic;
        // } else {
        //     $balance_service = $this->package->total_weight * $incomeCubic;
        // }

        $cubic = PricingCalculator::cubicCalculate($this->package->items);
        $balance_service = Partner::FEE_CUBIC * $cubic;

        $this
            ->setBalance($balance_service)
            ->setType(History::TYPE_DEPOSIT)
            ->setDescription($isTransit ? History::DESCRIPTION_TRANSIT : History::DESCRIPTION_SERVICE)
            ->setServiceType(History::DESCRIPTION_SERVICE_CUBIC)
            ->setAttributes()
            ->recordHistory();

        return $balance_service;
    }

    /**
     * Get Transit Tier Price Of Partner Transporter.
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
     * With A Single Packages.
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
     * With A Multiple Packages.
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

    protected function getTierTypeDooring($tier, $price)
    {
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

    /**
     * Check minimal charge in the some partner
     */
    protected function checkMinimalChargeWeight($partnerCode, $manifestWeight): int
    {
        switch (true) {
                // case $partnerCode === 'MTM-TNA-01':
                //     if ($manifestWeight < 50) {
                //         $manifestWeight = 50;
                //     }
                //     break;
            case $partnerCode === 'MT-JKT-13':
                if ($manifestWeight < 20) {
                    $manifestWeight = 20;
                }
                break;
                // case $partnerCode === 'MT-JKT-06':
                //     if ($manifestWeight < 100) {
                //         $manifestWeight = 100;
                //     }
                //     break;
            case $partnerCode === 'MTM-CKR-01':
                if ($manifestWeight < 50) {
                    $manifestWeight = 50;
                }
                break;
            default:
                if ($manifestWeight < 10) {
                    $manifestWeight = 10;
                }
                break;
        }

        return $manifestWeight;
    }

    /**
     * Income charge partner from 5% - 20%
     */
    protected function getIncomeChargePartner(int $totalWeight, $serviceFee): int
    {
        switch (true) {
            case $totalWeight > 99 && $totalWeight <= 499:
                $fee = $serviceFee * 0.05;
                break;
            case $totalWeight > 499 && $totalWeight <= 999:
                $fee = $serviceFee * 0.10;
                break;
            case $totalWeight > 999:
                $fee = $serviceFee * 0.20;
                break;
            default:
                $fee = 0;
                break;
        }

        if ($fee != 0) {
            $this
                ->setBalance($fee)
                ->setType(History::TYPE_CHARGE)
                ->setDescription(History::DESCRIPTION_ADDITIONAL)
                ->setAttributes()
                ->recordHistory();
        }

        return $fee;
    }
}
