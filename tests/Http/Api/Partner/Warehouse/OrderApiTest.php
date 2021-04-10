<?php

namespace Tests\Http\Api\Partner\Warehouse;

use App\Events\Packages\PackageAlreadyPackedByWarehouse;
use App\Events\Packages\WarehouseIsStartPacking;
use Database\Seeders\Packages\PostPaymentSeeder;
use Tests\TestCase;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use App\Models\Deliveries\Delivery;
use App\Casts\Package\Items\Handling;
use Illuminate\Support\Facades\Event;
use App\Models\Partners\Pivot\UserablePivot;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Events\Packages\PackageEstimatedByWarehouse;
use App\Events\Packages\WarehouseIsEstimatingPackage;
use Database\Seeders\Packages\WarehouseInChargeSeeder;
use App\Http\Controllers\Api\Partner\Warehouse\OrderController;

class OrderApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public bool $seed = true;

    public function test_can_get_list_order()
    {
        $this->seed(WarehouseInChargeSeeder::class);

        $user = $this->getUser(Partner::TYPE_BUSINESS, UserablePivot::ROLE_WAREHOUSE);
        $this->actingAs($user);

        $startRequest = function ($uri) {
            $response = $this->getJson($uri);
            $response->assertOk();
        };

        $startRequest(action([OrderController::class, 'index'], ['delivery_type' => Delivery::TYPE_PICKUP]));
        $startRequest(action([OrderController::class, 'index'], ['delivery_type' => Delivery::TYPE_TRANSIT]));
    }

    public function test_can_fire_event_estimating()
    {
        $this->seed(WarehouseInChargeSeeder::class);

        $user = $this->getUser(Partner::TYPE_BUSINESS, UserablePivot::ROLE_WAREHOUSE);
        $this->actingAs($user);

        $packageHash = $this->getJson(route('api.partner.warehouse.order', ['status' => Package::STATUS_WAITING_FOR_ESTIMATING]))->json('data.0.hash');

        Event::fake();

        $response = $this->patchJson(route('api.partner.warehouse.order.estimating', [
            'package_hash' => $packageHash,
        ]));

        $response->assertOk();

        Event::assertDispatched(WarehouseIsEstimatingPackage::class);
    }

    public function test_can_update_order()
    {
        $this->seed(WarehouseInChargeSeeder::class);

        $user = $this->getUser(Partner::TYPE_BUSINESS, UserablePivot::ROLE_WAREHOUSE);
        $this->actingAs($user);

        $package = $this->getEstimatingPackage();

        $uri = action([OrderController::class, 'update'], ['package_hash' => $package->hash]);

        $response = $this->putJson($uri, [
            'handling' => $chosenHandling = $this->faker->randomElements(Handling::getTypes()),
        ]);

        $response->assertSuccessful();
        $this->assertEquals($response->json('data.handling'), $chosenHandling);
    }

    public function test_can_fire_event_estimated()
    {
        $this->seed(WarehouseInChargeSeeder::class);

        $user = $this->getUser(Partner::TYPE_BUSINESS, UserablePivot::ROLE_WAREHOUSE);
        $this->actingAs($user);

        $packageHash = $this->getEstimatingPackage()->hash;

        Event::fake();

        $response = $this->patchJson(route('api.partner.warehouse.order.estimated', [
            'package_hash' => $packageHash,
        ]));

        $response->assertOk();

        Event::assertDispatched(PackageEstimatedByWarehouse::class);
    }

    public function test_can_fire_event_packing()
    {
        $this->seed(PostPaymentSeeder::class);

        /** @var Package $package */
        $package = Package::query()->where('status', Package::STATUS_WAITING_FOR_PACKING)->first();

        $packageHash = $package->hash;

        $user = $package
            ->deliveries()
            ->first()
            ->partner->users()->wherePivot('role', UserablePivot::ROLE_WAREHOUSE)->first();

        $this->actingAs($user);

        Event::fake();

        $response = $this->patchJson(route('api.partner.warehouse.order.packing', [
            'package_hash' => $packageHash,
        ]));

        $response->assertOk();

        $response = $this->patchJson(route('api.partner.warehouse.order.packed', [
            'package_hash' => $packageHash,
        ]));

        $response->assertOk();

        Event::assertDispatched(WarehouseIsStartPacking::class);
        Event::assertDispatched(PackageAlreadyPackedByWarehouse::class);
    }

    private function getEstimatingPackage(): Package
    {
        $packageHash = $this->getJson(route('api.partner.warehouse.order', ['status' => Package::STATUS_WAITING_FOR_ESTIMATING]))->json('data.0.hash');

        return Package::byHashOrFail($this->patchJson(route('api.partner.warehouse.order.estimating', ['package_hash' => $packageHash]))->json('data.hash'));
    }
}
