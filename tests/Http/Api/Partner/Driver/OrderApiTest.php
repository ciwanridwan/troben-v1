<?php

namespace Tests\Http\Api\Partner\Driver;

use App\Models\Deliveries\Delivery;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\Packages\AssignedPackagesSeeder;

class OrderApiTest extends TestCase
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

    public function test_can_get_all_order()
    {
        $uri = route('api.partner.driver.order');

        $response = $this->getJson($uri);

        $response->assertSuccessful();
    }

    public function test_can_filter_order()
    {
        $this->getJson(route('api.partner.driver.order', ['delivery_status' => Delivery::STATUS_FINISHED]))->assertJsonCount(0, 'data');

        // pick one from list all response
        $deliveryHash = $this->getJson(route('api.partner.driver.order', ['delivery_status' => Delivery::STATUS_ACCEPTED]))->json('data.0.hash');

        $this->patchJson(route('api.partner.driver.order.pickup.loaded', ['delivery_hash' => $deliveryHash]));
        $this->getJson(route('api.partner.driver.order', ['delivery_status' => Delivery::STATUS_EN_ROUTE]))->assertJsonCount(1, 'data');

        $this->patchJson(route('api.partner.driver.order.pickup.unloaded', ['delivery_hash' => $deliveryHash]));
        $this->getJson(route('api.partner.driver.order', ['delivery_status' => Delivery::STATUS_FINISHED]))->assertJsonCount(1, 'data');
    }
}
