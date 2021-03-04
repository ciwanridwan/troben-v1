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
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InventoryCreationManyTest extends TestCase
{
    use RefreshDatabase, DispatchesJobs, WithFaker;

    private $data;
    private Partner $partner;
    private Inventory $inventory;

    public function setUp(): void
    {
        parent::setUp();

        Partner::factory(1)->create([
            'type' => Partner::TYPE_BUSINESS,
        ]);
        $this->partner = Partner::first();
        $this->data = [
            [
                'name' => $this->faker->name,
                'capacity' => $this->faker->randomFloat(),
                'height' => $this->faker->randomFloat(),
                'count' => $this->faker->randomNumber(),
            ],
            [
                'name' => $this->faker->name,
                'capacity' => $this->faker->randomFloat(),
                'height' => $this->faker->randomFloat(),
                'count' => $this->faker->randomNumber(),
            ],
            [
                'name' => $this->faker->name,
                'capacity' => $this->faker->randomFloat(),
                'height' => $this->faker->randomFloat(),
                'count' => $this->faker->randomNumber(),
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
