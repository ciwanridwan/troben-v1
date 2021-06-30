<?php

namespace Tests\Jobs\Partners\Inventories;

use Tests\TestCase;
use App\Models\Partners\Partner;
use App\Models\Partners\Inventory;
use Illuminate\Support\Facades\Event;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Events\Inventory\ManyInventoryCreated;
use App\Jobs\Inventory\CreateManyNewInventory;
use App\Models\Partners\Warehouse;
use Database\Seeders\UsersTableSeeder;
use Database\Seeders\WarehousesTableSeeder;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InventoryCreationManyTest extends TestCase
{
    use RefreshDatabase, DispatchesJobs, WithFaker;

    private $data;
    private Partner $partner;
    private Inventory $inventory;
    private Warehouse $warehouse;

    public bool $seed = true;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed(WarehousesTableSeeder::class);
        $this->partner = Partner::query()->where('type', Partner::TYPE_POOL)->whereHas('warehouses')->first();
        $this->warehouse = $this->partner->warehouses->first();
        $this->data = [
            [
                'name' => $this->faker->name,
                'capacity' => $this->faker->randomFloat(),
                'height' => $this->faker->randomFloat(),
                'qty' => $this->faker->randomNumber(),
                'warehouse_id' => $this->warehouse->id
            ],
            [
                'name' => $this->faker->name,
                'capacity' => $this->faker->randomFloat(),
                'height' => $this->faker->randomFloat(),
                'qty' => $this->faker->randomNumber(),
                'warehouse_id' => $this->warehouse->id
            ],
            [
                'name' => $this->faker->name,
                'capacity' => $this->faker->randomFloat(),
                'height' => $this->faker->randomFloat(),
                'qty' => $this->faker->randomNumber(),
                'warehouse_id' => $this->warehouse->id
            ],
        ];
    }

    public function test_on_valid_data()
    {
        Event::fake();

        $job = new CreateManyNewInventory($this->partner, $this->data);

        $this->assertTrue($this->dispatch($job));

        $this->assertInstanceOf(Collection::class, $job->inventories);

        $this->assertTrue($job->finish);

        $this->assertDatabaseCount('inventories', $job->inventories->count());

        Event::assertDispatched(ManyInventoryCreated::class);
    }

    public function test_on_missing_data()
    {
        $this->expectException(ValidationException::class);
        Event::fake();

        $data = collect($this->data)->map(fn ($item) => ['name' => $item['name'], 'capacity' => $item['capacity']])->toArray();

        $job = new CreateManyNewInventory($this->partner, $data);
        $this->assertTrue($this->dispatch($job));

        Event::assertNotDispatched(ManyInventoryCreated::class);
    }
}
