<?php

namespace Tests\Feature\Partners\Warehouse;

use Exception;
use Tests\TestCase;
use Illuminate\Support\Arr;
use App\Models\Partners\Partner;
use App\Jobs\Partners\CreateNewPartner;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Jobs\Partners\Warehouse\CreateNewWarehouse;

class WarehouseCreationTest extends TestCase
{
    use RefreshDatabase, DispatchesJobs;

    private $data;
    private $partner;

    public function setUp(): void
    {
        parent::setUp();
        $partner_data = [
            'name' => 'PT Tambak',
            'code' => 'MB-TBK-0001',
            'contact_email' => 'tambak@email.com',
            'contact_phone' => '87287281109',
            // 'contact_phone' => null,
            'address' => 'jalan tambak',
            // 'address' => null,
            'geo_location' => null,
            'type' => Partner::TYPE_POOL,
        ];
        $this->data = [
            'geo_province_id' => '1',
            'geo_regency_id' => '1',
            'geo_district_id' => '1',
            'code' => '1',
            'name' => 'warehouse',
            'address' => 'Jl.an',
            'geo_area' => null,
            'is_pool' => true,
            'is_counter' => false,
        ];
        $job = new CreateNewPartner($partner_data);
        $this->dispatch($job);
        $this->partner = $job->partner;
    }

    /**
     * valid data test.
     *
     * @return void
     */
    public function test_on_valid_data()
    {
        $this->withoutExceptionHandling();
        // seed geo
        $this->seed();

        try {
            $job = new CreateNewWarehouse($this->partner, $this->data);
            $response = $this->dispatch($job);
            $this->assertTrue($response);

            $warehouse = $job->warehouse;
            $this->assertDatabaseHas('warehouses', $warehouse->toArray());
        } catch (Exception $e) {
            $this->assertNotInstanceOf(ValidationException::class, $e);
        }
    }

    /**
     * missing data test.
     *
     * @return void
     */
    public function test_on_missing_data()
    {
        $this->withoutExceptionHandling();

        try {
            $job = new CreateNewWarehouse($this->partner, []);
            $response = $this->dispatch($job);
            $this->assertFalse($response);
        } catch (Exception $e) {
            $this->assertInstanceOf(ValidationException::class, $e);
            foreach (Arr::only($this->data, ['code', 'name']) as $key => $value) {
                $this->assertArrayHasKey($key, $e->errors());
            }
        }
    }

    /**
     * missing data test.
     *
     * @return void
     */
    public function test_on_invalid_data()
    {
        $this->withoutExceptionHandling();

        // seed geo
        $this->seed();

        try {
            $job = new CreateNewWarehouse($this->partner, Arr::except($this->data, ['name']));
            $response = $this->dispatch($job);
            $this->assertFalse($response);
        } catch (Exception $e) {
            $this->assertInstanceOf(ValidationException::class, $e);
            $this->assertArrayHasKey('name', $e->errors());
            foreach (Arr::except($this->data, ['name']) as $key => $value) {
                $this->assertArrayNotHasKey($key, $e->errors());
            }
        }
    }
}
