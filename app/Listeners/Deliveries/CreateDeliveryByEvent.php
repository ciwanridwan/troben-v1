<?php

namespace App\Listeners\Deliveries;

use App\Events\Packages\PackageCancelMethodSelected;
use App\Jobs\Deliveries\Actions\CreateNewManifestForReturnPackage;
use App\Jobs\Deliveries\CreateNewDelivery;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\InteractsWithQueue;

class CreateDeliveryByEvent
{
    use DispatchesJobs;
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        switch (true) {
            case $event instanceof PackageCancelMethodSelected:
                /** @var Package $package */
                $package = $event->package;
                /** @var Partner $partner */
                $partner = $package->picked_up_by->first()->partner;

                // todo: create delivery from existing package
                // desc: [type: return, status: waiting_assign_transporter]
                // todo: assign delivery to existing partner picked up package
                $job = new CreateNewManifestForReturnPackage($partner, $package);
                $this->dispatch($job);

                /** @var Delivery $delivery */
                $delivery = $job->delivery;
                $package = $job->package;
                break;

            default:
                # code...
                break;
        }
    }
}
