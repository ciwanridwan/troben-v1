<?php

namespace App\Jobs\Deliveries\Actions;

use App\Jobs\Deliveries\CreateNewDelivery;
use App\Models\Deliveries\Delivery;
use App\Models\Partners\Partner;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateNewManifestForReturnPackage
{
    use Dispatchable;

    public Delivery $delivery;

    private array $attributes;

    /**
     * @var \App\Models\Partners\Partner
     */
    private Partner $originPartner;



    /**
     * @param Partner $originPartner
     * @param array $inputs
     */
    public function __construct(Partner $originPartner)
    {
        $this->originPartner = $originPartner;
        $this->attributes = [
            'type' => Delivery::TYPE_RETURN,
            'status' => Delivery::STATUS_WAITING_ASSIGN_TRANSPORTER
        ];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $job = new CreateNewDelivery($this->attributes, null, $this->originPartner);
        $this->dispatch($job);
        $this->delivery = $job->delivery;
        dd($this->delivery);
    }
}
