<?php

namespace Tests\Http\Api\Partner\Driver\Order;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Events\Deliveries\Pickup\PackageLoadedByDriver;
use App\Events\Deliveries\Pickup\DriverArrivedAtWarehouse;
use App\Events\Deliveries\Pickup\DriverArrivedAtPickupPoint;
use App\Events\Deliveries\Pickup\DriverUnloadedPackageInWarehouse;
use App\Jobs\Deliveries\Actions\AssignDriverToDelivery;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Package;
use App\Models\Partners\Pivot\UserablePivot;
use Database\Seeders\Packages\InTransit\Drivers\DriverArrivedAtOriginWarehouseSeeder;
use Database\Seeders\Packages\InTransit\Warehouses\AssignDriverToDeliverySeeder;
use Database\Seeders\Packages\InTransit\Warehouses\PackageAlreadyPackedByWarehouseSeeder;
use Database\Seeders\Packages\InTransit\Warehouses\PackageAttachedToDeliverySeeder;
use Database\Seeders\Packages\InTransit\Warehouses\WarehouseAssignPackageToManifestSeeder;
use Database\Seeders\Packages\InTransit\Warehouses\WarehouseIsStartPackingSeeder;

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

        // Event::fake();

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

        Event::assertDispatched(DriverArrivedAtPickupPoint::class);
        Event::assertDispatched(PackageLoadedByDriver::class);
        Event::assertDispatched(DriverArrivedAtWarehouse::class);
        Event::assertDispatched(DriverUnloadedPackageInWarehouse::class);
    }
}
