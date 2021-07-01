<?php

namespace Tests\Http\Api\Partner\Driver\Order;

use App\Events\Deliveries\Transit\DriverArrivedAtDestinationWarehouse;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Events\Deliveries\Transit\PackageLoadedByDriver;
use App\Events\Deliveries\Transit\DriverArrivedAtOriginWarehouse;
use App\Events\Deliveries\Transit\DriverUnloadedPackageInDestinationWarehouse;
use App\Models\Deliveries\Delivery;
use Database\Seeders\Packages\InTransit\Warehouses\AssignDriverToDeliverySeeder;

class TransitApiTest extends TestCase
{
    use RefreshDatabase;

    public bool $seed = true;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AssignDriverToDeliverySeeder::class);

        /** @var User $user */
        $user = User::query()->whereHas('deliveries', fn ($query) => $query->where('type', Delivery::TYPE_TRANSIT))->first();

        $this->actingAs($user);
    }

    public function test_can_get_hit_patch_delivery()
    {
        $deliveryHash = $this->getJson(route('api.partner.driver.order'))->json('data.0.hash');

        Event::fake();

        $response = $this->patchJson(route('api.partner.driver.order.transit.arrived', [
            'delivery_hash' => $deliveryHash,
        ]));

        $response->assertSuccessful();

        /** @var Delivery $delivery */
        $delivery = Delivery::byHash($deliveryHash);
        $codes = $delivery->item_codes->pluck('content')->toArray();
        $response = $this->patchJson(route('api.partner.driver.order.transit.loaded', [
            'delivery_hash' => $deliveryHash
        ]), ['code' => $codes]);
        $response->assertSuccessful();


        $response = $this->patchJson(route('api.partner.driver.order.transit.finished', [
            'delivery_hash' => $deliveryHash,
        ]));

        $response->assertSuccessful();

        $response = $this->patchJson(route('api.partner.driver.order.transit.unloaded', [
            'delivery_hash' => $deliveryHash,
        ]));

        $response->assertSuccessful();

        Event::assertDispatched(DriverArrivedAtOriginWarehouse::class);
        Event::assertDispatched(PackageLoadedByDriver::class);
        Event::assertDispatched(DriverArrivedAtDestinationWarehouse::class);
        Event::assertDispatched(DriverUnloadedPackageInDestinationWarehouse::class);
    }
}
