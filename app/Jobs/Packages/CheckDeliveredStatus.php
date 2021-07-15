<?php

namespace App\Jobs\Packages;

use App\Casts\Package\Items\Handling;
use App\Events\Packages\PackageUpdated;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Package;
use App\Models\Partners\Transporter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
    public function __construct(Delivery $delivery, bool $isSeparated = null)
    {
        $this->delivery = $delivery;


    }

    public function handle()
    {
        $packages = $this->delivery->packages;

        $count = $packages->count();
        $check = 0;
        foreach ($packages as  $package) {
            if ($package['status'] == Package::STATUS_DELIVERED ){
                $check++;
            }
        }
        if ($count == $check){
            $this->delivery->status = Delivery::STATUS_FINISHED;
            $this->delivery->save();
        }
    }
}
