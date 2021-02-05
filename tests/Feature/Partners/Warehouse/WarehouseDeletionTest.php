<?php

namespace Tests\Feature\Partners\Warehouse;

use Tests\TestCase;
use App\Models\Partners\Partner;
use App\Models\Partners\Warehouse;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Jobs\Partners\Warehouse\DeleteExistingWarehouse;

class WarehouseDeletionTest extends TestCase
{
    use RefreshDatabase, DispatchesJobs;

    /**
     * Warehouse instance.
     * 
     * @var \App\Models\Partners\Warehouse
     */
    private Warehouse $warehouse;

    /**
     * Setup test environment.
     * 
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        Partner::factory(1)->create();
        Warehouse::factory(1)->create([
            'partner_id' => 1,
        ]);

        $this->warehouse = Warehouse::latest()->first();
    }

    public function test_on_valid_data()
    {
        $response = $this->dispatch(new DeleteExistingWarehouse($this->warehouse));
        $this->assertTrue($response);
        $this->assertDatabaseMissing('partners', $this->warehouse->toArray());
    }
}
