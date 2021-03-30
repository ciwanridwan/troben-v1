<?php

namespace Tests\Http\Api\Partner\Driver\Order;

use App\Events\Deliveries\Pickup\DriverArrivedAtPickupPoint;
use App\Events\Deliveries\Pickup\DriverArrivedAtWarehouse;
use App\Events\Deliveries\Pickup\DriverUnloadedPackageInWarehouse;
use App\Events\Deliveries\Pickup\PackageLoadedByDriver;
use App\Models\User;
use Database\Seeders\Packages\AssignedPackagesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class PickupApiTest extends TestCase
{
    use RefreshDatabase;

    public bool $seed = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AssignedPackagesSeeder::class);

        /** @var User $user */
        $user = User::query()->whereHas('deliveries')->first();

        $this->actingAs($user);
    }

    public function test_can_get_hit_patch_delivery()
    {
        $deliveryHash = $this->getJson(route('api.partner.driver.order'))->json('data.0.hash');

        Event::fake();

        $response = $this->patchJson(route('api.partner.driver.order.pickup.arrived', [
            'delivery_hash' => $deliveryHash,
        ]));

        $response->assertSuccessful();

        $response = $this->patchJson(route('api.partner.driver.order.pickup.loaded', [
            'delivery_hash' => $deliveryHash,
        ]));

        $response->assertSuccessful();

        $response = $this->patchJson(route('api.partner.driver.order.pickup.finished', [
            'delivery_hash' => $deliveryHash,
        ]));

        $response->assertSuccessful();

        $response = $this->patchJson(route('api.partner.driver.order.pickup.unloaded', [
            'delivery_hash' => $deliveryHash,
        ]));

        $response->assertSuccessful();

        Event::assertDispatched(DriverArrivedAtPickupPoint::class);
        Event::assertDispatched(PackageLoadedByDriver::class);
        Event::assertDispatched(DriverArrivedAtWarehouse::class);
        Event::assertDispatched(DriverUnloadedPackageInWarehouse::class);
    }
}
