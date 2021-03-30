<?php

namespace Tests\Http\Api\Partner\Warehouse;

use App\Events\Packages\PackageEstimatedByWarehouse;
use Database\Seeders\Packages\FinishedDeliveriesSeeder;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use App\Models\Partners\Partner;
use App\Models\Partners\Pivot\UserablePivot;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    public bool $seed = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(FinishedDeliveriesSeeder::class);

        $user = $this->getUser(Partner::TYPE_BUSINESS, UserablePivot::ROLE_WAREHOUSE);
        $this->actingAs($user);
    }

    public function test_can_get_list_order()
    {
        $uri = route('api.partner.warehouse.order');

        $response = $this->getJson($uri);

        $response->assertOk();
    }

    public function test_can_fire_event_estimated()
    {
        $packageHash = $this->getJson(route('api.partner.warehouse.order'))->json('data.0.hash');

        Event::fake();

        $response = $this->patchJson(route('api.partner.warehouse.order.estimated', [
            'package_hash' => $packageHash,
        ]));

        $response->assertOk();

        Event::assertDispatched(PackageEstimatedByWarehouse::class);
    }
}
