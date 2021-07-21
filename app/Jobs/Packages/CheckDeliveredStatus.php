<?php

namespace App\Jobs\Packages;

use App\Models\Deliveries\Delivery;
use App\Models\Packages\Package;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CheckDeliveredStatus implements ShouldQueue
{
    use Dispatchable;

    /**
     * Package instance.
     *
     * @var \App\Models\Packages\Package
     */
    public Delivery $delivery;

    private array $attributes;
    private ?bool $isSeparated;

    /**
     * CheckDeliveredStatus constructor.
     * @param Delivery $delivery
     * @param Package $package
     * @param array $inputs
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __construct(Delivery $delivery)
    {
        $this->delivery = $delivery;
    }

    public function handle()
    {
        $packages = $this->delivery->packages;

        $count = $packages->count();
        $check = 0;
        foreach ($packages as  $package) {
            if ($package['status'] == Package::STATUS_DELIVERED) {
                $check++;
            }
        }
        if ($count == $check) {
            $this->delivery->status = Delivery::STATUS_FINISHED;
            $this->delivery->save();
        }
    }
}
