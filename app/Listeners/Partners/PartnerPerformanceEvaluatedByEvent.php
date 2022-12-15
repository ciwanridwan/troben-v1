<?php

namespace App\Listeners\Partners;

use App\Events\Deliveries\Dooring\DriverDooringFinished;
use App\Events\Deliveries\Dooring\DriverUnloadedPackageInDooringPoint;
use App\Events\Deliveries\DriverAssignedOfTransit;
use App\Events\Deliveries\PartnerRequested;
use App\Events\Deliveries\Transit\DriverUnloadedPackageInDestinationWarehouse;
use App\Events\Deliveries\Transit\WarehouseUnloadedPackages;
use App\Events\Packages\WarehouseIsStartPacking;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Package;
use App\Models\Partners\Balance\History;
use App\Models\Partners\Partner;
use App\Models\Partners\Performances as Performance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PartnerPerformanceEvaluatedByEvent
{
    /**
     * Performance instance.
     *
     * @var Performance\PerformanceModel $performance
     */
    private Performance\PerformanceModel $performance;

    /**
     * Delivery instance.
     *
     * @var Delivery $delivery
     */
    private Delivery $delivery;

    /**
     * Attributes.
     *
     * @var array $attributes
     */
    private array $attributes;

    /**
     * Package instance.
     *
     * @var Package $package
     */
    private Package $package;

    /** Set date time now */
    private string $reach_at;

    /**
     * Partner Instance
     */
    private Partner $partner;

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(object $event): void
    {
        switch (true) {
            case $event instanceof DriverUnloadedPackageInDestinationWarehouse:
                $this->delivery = $event->delivery;

                $packages = $this->delivery->packages;
                $this->reach_at = Carbon::now();

                foreach ($packages as $package) {
                    /** @var Package $package */
                    $this->package = $package;

                    if ($this->reach_at > $this->package->partner_performance->deadline) {
                        $this->setPenaltyIncome($this->package);
                    }

                    if ($this->package->partner_performance !== null) {
                        $this
                            ->setPerformance($this->package->partner_performance)
                            ->updatePerformance();
                    }
                }

                if ($this->delivery->partner_performance !== null) {
                    $this
                        ->setPerformance($this->delivery->partner_performance)
                        ->updatePerformance();
                }
                break;
            case $event instanceof WarehouseUnloadedPackages || $event instanceof DriverDooringFinished:
                $this->delivery = $event->delivery;
                $this->reach_at = Carbon::now();
                if ($this->reach_at > $this->delivery->partner_performance->deadline) {
                    $this->setPenaltyIncome($this->delivery);
                }

                if ($this->delivery->partner_performance !== null) {
                    $this
                        ->setPerformance($this->delivery->partner_performance)
                        ->updatePerformance();
                }
                break;
            case $event instanceof WarehouseIsStartPacking:
                $this->package = $event->package;

                $this->reach_at = Carbon::now();
                $deadline = $this->package->partner_performance->deadline;
                if ($this->reach_at > $deadline) {
                    $this->setPenaltyIncome($this->package);
                }

                $this->setPerformance($this->package->partner_performance)->updatePerformance();
                break;
            case $event instanceof DriverAssignedOfTransit:
                $this->delivery = $event->delivery;
                if ($this->delivery->partner_performance !== null) {
                    $this
                        ->setPerformance($this->delivery->partner_performance)
                        ->updatePerformance();
                }
                break;
            case $event instanceof DriverUnloadedPackageInDooringPoint:
                $this->delivery = $event->delivery;
                if ($this->delivery->partner_performance !== null) {
                    $this
                        ->setPerformance($this->delivery->partner_performance)
                        ->updatePerformance();
                }
                break;
            case $event instanceof PartnerRequested:
                $this->delivery = $event->delivery;
                if ($this->delivery->partner_performance !== null) {
                    $this
                        ->setPerformance($this->delivery->partner_performance)
                        ->updatePerformance();
                }
                break;
            default:
                // to do default
                break;
        }
    }

    /**
     * @param Performance\PerformanceModel $performance
     * @return $this
     */
    protected function setPerformance(Performance\PerformanceModel $performance): self
    {
        $this->performance = $performance;
        return $this;
    }

    /**
     *  Updating performance that.
     */
    protected function updatePerformance(): void
    {
        $this->setAttributes();
        $is_package = $this->performance instanceof Performance\Package;

        if (!empty($this->attributes)) {
            $query = match ($is_package) {
                true => $this->package->partner_performance(),
                false => $this->delivery->partner_performance()
            };

            $query
                ->where($this->attributes)
                ->update([
                    'reached_at' => Carbon::now(),
                    'status' => Performance\PerformanceModel::STATUS_REACHED
                ]);
        }
    }

    protected function setAttributes(): void
    {
        $defaultAttribute = [
            'partner_id' => $this->performance->partner_id,
            'level' => $this->performance->level
        ];

        if ($this->performance instanceof Performance\Package) {
            $this->attributes = array_merge($defaultAttribute, ['package_id' => $this->performance->package_id]);
        } elseif ($this->performance instanceof Performance\Delivery) {
            $this->attributes = array_merge($defaultAttribute, ['delivery_id' => $this->performance->delivery_id]);
        } else {
            Log::info('performance: ', ['delivery' => $this->delivery->code->content, 'performance' => $this->performance]);
            $this->attributes = [];
        }
    }


    protected function setPenaltyIncome($type): void
    {
        switch (true) {
            case $type instanceof Package:
                $this->package = $type;
                $partnerId = $this->package->partner_performance->partner_id;

                $this->createHistory($this->package, $partnerId);
                break;
            case $type instanceof Delivery:
                $this->delivery = $type;
                $packages = $this->delivery->packages;

                foreach ($packages as $package) {
                    $this->package = $package;
                    $this->createHistory($this->package, $this->delivery->partner_performance->partner_id);
                }
                break;
            default:
                # code...
                break;
        }
    }


    protected function createHistory($package, $partnerId): void
    {
        $serviceFee = $package->service_price;
        $incomePenalty = $serviceFee * Partner::PENALTY_PERCENTAGE;

        History::create([
            'partner_id' => $partnerId,
            'package_id' => $package->id,
            'balance' => $incomePenalty,
            'type' => History::TYPE_PENALTY,
            'description' => History::DESCRIPTION_LATENESS,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        $this->updateBalancePartner($partnerId, $incomePenalty);
    }


    protected function updateBalancePartner($partnerId, $incomePenalty): void
    {
        $this->partner = Partner::find($partnerId);
        $this->partner->balance -= $incomePenalty;
        $this->partner->save();
    }
}
