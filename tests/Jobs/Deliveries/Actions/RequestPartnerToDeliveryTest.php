<?php

namespace Tests\Jobs\Deliveries\Actions;

use App\Jobs\Deliveries\Actions\RequestPartnerToDelivery;
use App\Models\Deliveries\Delivery;
use Database\Seeders\Packages\InTransit\PackageAssignedToManifestSeeder;
use Database\Seeders\Packages\InTransit\RequestPartnerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RequestPartnerToDeliveryTest extends TestCase
{
    use RefreshDatabase;

    public bool $seed = true;

    public function test_on_valid_data()
    {
        $this->seed(PackageAssignedToManifestSeeder::class);

        $delivery = Delivery::query()->where('type', Delivery::TYPE_TRANSIT)->where('status', Delivery::STATUS_WAITING_ASSIGN_TRANSPORTER)->first();

        $job = new RequestPartnerToDelivery($delivery);

        dispatch_now($job);

        // get first partner
        $this->assertSame(Delivery::STATUS_WAITING_ASSIGN_PARTNER, $delivery->fresh()->status);
    }
}
