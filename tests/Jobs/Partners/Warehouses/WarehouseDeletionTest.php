<?php

namespace Tests\Jobs\Partners\Warehouses;

use Tests\TestCase;
use Illuminate\Support\Arr;
use App\Models\Partners\Partner;
use App\Models\Partners\Warehouse;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Events\Partners\Warehouse\WarehouseDeleted;
use App\Jobs\Partners\Warehouse\DeleteExistingWarehouse;

class WarehouseDeletionTest extends TestCase
{
    use RefreshDatabase, DispatchesJobs;

    public function test_on_valid_data()
    {
        Event::fake();

        Partner::factory(1)
            ->has(Warehouse::factory()->count(1))
            ->create();

        /** @var \App\Models\Partners\Warehouse $warehouse */
        $warehouse = Warehouse::query()->first();

        $response = $this->dispatch(new DeleteExistingWarehouse($warehouse));
        $this->assertTrue($response);
        $this->assertDatabaseMissing('partners', Arr::only($warehouse->toArray(), 'code'));

        Event::assertDispatched(WarehouseDeleted::class);
    }
}
