<?php

namespace Tests\Http\Api\Partner\Warehouse\Item;

use App\Models\Customers\Customer;
use App\Models\Handling;
use App\Models\Packages\Package;
use Database\Seeders\Packages\FinishedDeliveriesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ItemApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public bool $seed = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(FinishedDeliveriesSeeder::class);
    }

    public function test_can_create_item()
    {
        /** @var Customer $customer */
        $customer = Customer::query()->first();

        $this->actingAs($customer);

        /** @var Package $package */
        $package = $customer->packages()->first();

        $newData = [
            'qty' => $this->faker->numberBetween(1, 3),
            'name' => $this->faker->text(9),
            'weight' => $this->faker->numberBetween(1, 10),
            'width' => $this->faker->numberBetween(1, 40),
            'length' => $this->faker->numberBetween(1, 40),
            'height' => $this->faker->numberBetween(1, 40),
            'handling' => Handling::query()->take($this->faker->numberBetween(1, Handling::query()->count()))->get()->map->id->toArray(),
        ];

        $url = route('api.order.item.store', [$package->hash]);
        $headers = $this->getCustomersHeader();

        $totalItems = $package->items()->count();

        $response = $this->postJson($url, $newData, $headers);

        $response->assertSuccessful();
        $this->assertTrue($totalItems + 1 === $package->items()->count());
    }

    public function test_can_update_items()
    {
        /** @var Customer $customer */
        $customer = Customer::query()->first();

        $this->actingAs($customer);

        /** @var Package $package */
        $package = $customer->packages()->first();

        /** @var \App\Models\Packages\Item $item */
        $item = $package->items()->inRandomOrder()->first();

        $newData = [
            'qty' => $this->faker->numberBetween(1, 3),
            'name' => $this->faker->text(9),
            'weight' => $this->faker->numberBetween(1, 10),
            'width' => $this->faker->numberBetween(1, 40),
            'length' => $this->faker->numberBetween(1, 40),
            'height' => $this->faker->numberBetween(1, 40),
            'handling' => Handling::query()->take($this->faker->numberBetween(1, Handling::query()->count()))->get()->map->id->toArray(),
        ];

        $url = route('api.order.item.update', [$package->hash, $item->hash]);
        $headers = $this->getCustomersHeader();

        $response = $this->putJson($url, $newData, $headers);

        $response->assertSuccessful();
    }

    public function test_can_delete_item_from_package()
    {
        /** @var Customer $customer */
        $customer = Customer::query()->first();

        $this->actingAs($customer);

        /** @var Package $package */
        $package = $customer->packages()->first();

        /** @var \App\Models\Packages\Item $item */
        $item = $package->items()->inRandomOrder()->first();

        $url = route('api.order.item.destroy', [$package->hash, $item->hash]);
        $headers = $this->getCustomersHeader();

        $response = $this->deleteJson($url, [], $headers);

        $response->assertSuccessful();

        $this->assertDatabaseMissing('package_items', [
            'id' => $item->id,
        ]);
    }
}
