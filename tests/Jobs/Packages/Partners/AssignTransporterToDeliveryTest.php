<?php

namespace Tests\Jobs\Packages\Partners;

use Tests\TestCase;
use App\Models\User;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use App\Models\Deliveries\Delivery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\Packages\AssignedPackagesSeeder;
use App\Jobs\Deliveries\Actions\AssignTransporterToDelivery;

class AssignTransporterToDeliveryTest extends TestCase
{
    use RefreshDatabase;

    public bool $seed = true;

    public function test_on_valid_data()
    {
        $this->seed(AssignedPackagesSeeder::class);

        /** @var User $user */
        $user = User::query()->first();

        /** @var Package $package */
        $package = Package::query()->where('status', Package::STATUS_PENDING)->first();
        /** @var Partner $partner */
        $partner = $user->partners()->first();

        /** @var \App\Models\Deliveries\Delivery $delivery */
        $delivery = $package->deliveries()->first();

        /** @var \App\Models\Partners\Transporter $transporter */
        $transporter = $partner->transporters()->first();

        $job = new AssignTransporterToDelivery($delivery, $transporter);

        dispatch_now($job);

        $this->assertDatabaseHas('deliveries', [
            'transporter_id' => $transporter->id,
            'type' => Delivery::TYPE_PICKUP,
            'status' => Delivery::STATUS_ACCEPTED,
        ]);

        $this->assertTrue($transporter->deliveries()->whereHas('packages', fn (Builder $builder) => $builder->where('id', $package->id)) !== null);
    }
}
