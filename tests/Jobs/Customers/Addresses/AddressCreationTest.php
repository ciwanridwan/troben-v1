<?php

namespace Tests\Jobs\Customers\Addresses;

use Tests\TestCase;
use Illuminate\Support\Arr;
use App\Jobs\Customers\CreateNewCustomer;
use Database\Seeders\GeoTableSimpleSeeder;
use App\Jobs\Customers\CreateCustomerAddress;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AddressCreationTest extends TestCase
{
    use RefreshDatabase, DispatchesJobs;

    private array $data;

    private $customer;

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function setUp(): void
    {
        parent::setUp();

        // create new customer
        $customer_data = [
            'name' => 'username test',
            'phone' => '08512345679',
            'password' => 'aLphAnumeric123',
            'email' => 'email@test.com',
        ];
        $job = new CreateNewCustomer($customer_data);
        $this->dispatch($job);
        // get customer
        $this->customer = $job->customer;

        $this->data = [
            'name' => 'test',
            'address' => 'Jl. Test',
            'geo_location' => null,
            'geo_province_id' => '1',
            'geo_regency_id' => '1',
            'geo_district_id' => '1',
            'is_default' => null,
        ];
    }

    /** @test */
    public function test_on_valid_data()
    {
        // seed geo
        $this->seed(GeoTableSimpleSeeder::class);

        $this->withoutExceptionHandling();

        try {
            $job = new CreateCustomerAddress($this->customer, $this->data);
            $response = $this->dispatch($job);
            $customer_address = $job->customer_address;
            $this->assertTrue($response);

            // assert in db
            $this->assertDatabaseHas('customer_address', $customer_address->toArray());
        } catch (\Exception $e) {
            $this->assertNotInstanceOf(ValidationException::class, $e);
        }
    }

    /** @test */
    public function test_on_missing_data()
    {
        // seed geo
        $this->seed(GeoTableSimpleSeeder::class);

        $this->withoutExceptionHandling();

        try {
            $response = $this->dispatch(new CreateCustomerAddress($this->customer, []));
            $this->assertFalse($response);
        } catch (\Exception $e) {
            $this->assertInstanceOf(ValidationException::class, $e);
            foreach (Arr::except($this->data, ['geo_location', 'is_default']) as $key => $value) {
                $this->assertArrayHasKey($key, $e->errors());
            }
        }
    }

    /** @test */
    public function test_on_invalid_data()
    {
        $this->withoutExceptionHandling();

        try {
            $invalid_data = [
                'name' => '',
                'address' => '',
                'is_default' => 50,
                'geo_province_id' => 'a',
                'geo_regency_id' => 'b',
                'geo_district_id' => 'c',
            ];
            $response = $this->dispatch(new CreateCustomerAddress($this->customer, $invalid_data));
            $this->assertFalse($response);
        } catch (\Exception $e) {
            $this->assertInstanceOf(ValidationException::class, $e);
            foreach (Arr::except($this->data, ['geo_location']) as $key => $value) {
                $this->assertArrayHasKey($key, $e->errors());
            }
        }
    }
}
