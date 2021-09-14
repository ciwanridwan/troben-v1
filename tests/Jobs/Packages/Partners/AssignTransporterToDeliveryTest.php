<?php

namespace Tests\Jobs\Packages\Partners;

use Tests\TestCase;
use App\Models\User;
use App\Models\Packages\Package;
use App\Models\Deliveries\Delivery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\Packages\AssignedPackagesSeeder;
use App\Jobs\Deliveries\Actions\AssignDriverToDelivery;

class AssignTransporterToDeliveryTest extends TestCase
{
    use RefreshDatabase;

    public bool $seed = true;

    public function test_on_valid_data()
    {
        $this->seed(AssignedPackagesSeeder::class);

        /** @var Package $package */
        $package = Package::query()->where('status', Package::STATUS_PENDING)->first();

        /** @var \App\Models\Deliveries\Delivery $delivery */
        $delivery = $package->deliveries()->first();

        $partner = $delivery->partner;

        /** @var \App\Models\Partners\Transporter $transporter */
        $transporter = $partner->transporters()->first();

        /** @var User $driver */
        $driver = $transporter->drivers->first();
        $method = 'partner';
        $job = new AssignDriverToDelivery($delivery, $driver->pivot, $method);

        dispatch_now($job);

        $this->assertDatabaseHas('deliveries', [
            'userable_id' => (string) $driver->pivot->id,
            'type' => Delivery::TYPE_PICKUP,
            'status' => Delivery::STATUS_ACCEPTED,
        ]);

        $this->assertNotNull($driver->deliveries()->where('deliveries.id', $delivery->id)->first());
        $this->assertNotNull($transporter->deliveries()->where('deliveries.id', $delivery->id)->first());
    }
}
