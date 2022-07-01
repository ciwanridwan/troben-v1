<?php

namespace App\Listeners\Partners;

use App\Events\Deliveries\Dooring\DriverDooringFinished;
use App\Events\Deliveries\Transit\DriverUnloadedPackageInDestinationWarehouse;
use App\Events\Deliveries\Transit\WarehouseUnloadedPackages;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Package;
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

                foreach ($packages as $package) {
                    /** @var Package $package */
                    $this->package = $package;
                    $this
                        ->setPerformance($this->package->partner_performance)
                        ->updatePerformance();
                }

                $this
                    ->setPerformance($this->delivery->partner_performance)
                    ->updatePerformance();
                break;
            case $event instanceof WarehouseUnloadedPackages || $event instanceof DriverDooringFinished:
                $this->delivery = $event->delivery;

                $this
                    ->setPerformance($this->delivery->partner_performance)
                    ->updatePerformance();
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

        if (! empty($this->attributes)) {
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
}
