<?php

namespace Tests\Feature\Partners\Warehouse;

use Tests\TestCase;
use Illuminate\Support\Arr;
use App\Models\Partners\Partner;
use App\Models\Partners\Warehouse;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Bus\DispatchesJobs;
use \Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Jobs\Partners\Warehouse\UpdateExistingWarehouse;

class WarehouseUpdatingTest extends TestCase
{
    use RefreshDatabase, DispatchesJobs, WithFaker;

    /**
     * Warehouse instance.
     * 
     * @var App\Models\Partners\Warehouse
     */
    private Warehouse $warehouse;

    public bool $seed = true;

    private $data;

    /**
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

        $this->data = [
            'partner_id' => 1,
            'geo_province_id' => null,
            'geo_regency_id' => null,
            'geo_district_id' => null,
            'code' => $this->faker->userName,
            'name' => $this->faker->name,
            'address' => $this->faker->address,
            'geo_area' => null,
            'is_pool' => $this->faker->boolean(),
            'is_counter' => $this->faker->boolean(),
        ];
    }
    
    public function test_on_valid_data()
    {
        $this->withoutExceptionHandling();

        try {
            $response = $this->dispatch(new UpdateExistingWarehouse($this->warehouse, $this->data));
            $this->assertTrue($response);
            $this->assertDatabaseHas('warehouses', $this->data);
        } catch (\Exception $e) {
            $this->assertNotInstanceOf(ValidationException::class, $e);
        }
    }

    public function test_on_missing_data()
    {
        $this->withoutExceptionHandling();

        try {
            $response = $this->dispatch(new UpdateExistingWarehouse($this->warehouse));
            $this->assertTrue($response);
        } catch (\Exception $e) {
            $this->assertNotInstanceOf(ValidationException::class, $e);
        }
    }

    public function test_on_invalid_data()
    {
        $this->withoutExceptionHandling();

        $invalid_field_name = 'is_pool';

        $data = $this->data;
        $data[$invalid_field_name] = $this->faker->name;

        try {
            $response = $this->dispatch(new UpdateExistingWarehouse($this->warehouse, $data));
            $this->assertTrue($response);
            $this->assertDatabaseHas('customers', $this->data);
        } catch (\Exception $e) {
            $this->assertInstanceOf(ValidationException::class, $e);
            $this->assertArrayHasKey($invalid_field_name, $e->errors());
            foreach (Arr::except($data, $invalid_field_name) as $key => $value) {
                $this->assertArrayNotHasKey($key, $e->errors());
            }
        }
    }
}
