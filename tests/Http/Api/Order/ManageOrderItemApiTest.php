<?php

namespace Tests\Http\Api\Order;

use Tests\TestCase;
use App\Models\Packages\Package;
use App\Models\Customers\Customer;
use App\Casts\Package\Items\Handling;
use Illuminate\Foundation\Testing\WithFaker;
use Database\Seeders\Packages\PackagesTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;

class ManageOrderItemApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public bool $seed = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PackagesTableSeeder::class);
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
            'price' => $this->faker->randomElement([100000, 2000000, 4000000, 5000000, 19000, 900000]),
            'handling' => $this->faker->randomElements(Handling::getTypes()),
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

        $newData = [];
        $newData[] = [
            'hash' => $item->hash,
            'qty' => $this->faker->numberBetween(1, 3),
            'name' => $this->faker->text(9),
            'weight' => $this->faker->numberBetween(1, 10),
            'width' => $this->faker->numberBetween(1, 40),
            'length' => $this->faker->numberBetween(1, 40),
            'height' => $this->faker->numberBetween(1, 40),
            'price' => $this->faker->randomElement([100000, 2000000, 4000000, 5000000, 19000, 900000]),
            'handling' => $this->faker->randomElements(Handling::getTypes()),
        ];

        $url = route('api.order.item.update', [$package->hash]);
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
