<?php

namespace App\Listeners\Packages;

use App\Jobs\Payments\CreateNewPayment;
use App\Models\Packages\Package;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class MakeInvoiceFromPackage
{
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
        if (property_exists($event, 'package') && $event->package instanceof Package && $event->package->payment_status !== Package::PAYMENT_STATUS_DRAFT) {
            /** @var Package $package */
            $package = $event->package;
            // $job = new CreateNewPayment($package);
        }
    }
}
