<?php

namespace Tests\Http\Api\Order;

use Tests\TestCase;
use App\Models\Handling;
use App\Models\Packages\Package;
use Database\Seeders\PackagesSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ManageOrderItemApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public bool $seed = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PackagesSeeder::class);
    }

    public function test_can_update_items()
    {
        /** @var Package $package */
        $package = Package::query()->first();
        /** @var \App\Models\Packages\Item $item */
        $item = $package->items()->inRandomOrder()->first();

        $newData = [
            'qty' => $this->faker->numberBetween(1, 3),
            'name' => $this->faker->text(9),
            'weight' => $this->faker->numberBetween(1, 10),
            'width' => $this->faker->numberBetween(1, 40),
            'length' => $this->faker->numberBetween(1, 40),
            'height' => $this->faker->numberBetween(1, 40),
            'handling' => Handling::query()->take($this->faker->numberBetween(1, Handling::query()->count()))->get(),
        ];

        $url = route('api.order.item.update', [$package->hash, $item->hash]);
        $headers = $this->getCustomersHeader();

        $response = $this->putJson($url, $newData, $headers);

        $response->assertSuccessful();
    }

    public function test_can_delete_item_from_package()
    {
        /** @var Package $package */
        $package = Package::query()->first();
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
