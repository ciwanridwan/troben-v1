<?php

namespace Tests\Feature\Customers;

use Tests\TestCase;
use Illuminate\Support\Arr;
use App\Models\Customers\Customer;
use App\Jobs\Customers\CreateNewCustomer;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Jobs\Customers\UpdateExistingCustomer;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CustomerUpdatingTest extends TestCase
{
    use RefreshDatabase, DispatchesJobs;

    private $customer;
    private $data;
    public function setUp(): void
    {
        parent::setUp();
        $customer_data = [
            'name' => 'username test',
            'phone' => '08512345679',
            'password' => 'aLphAnumeric123',
            'email' => 'email@test.com',
        ];

        // insert on refresh database
        $this->dispatch(new CreateNewCustomer($customer_data));
        $this->customer = Customer::first();
        $this->data = [
            'name' => 'username test update',
            'phone' => '085987654331',
            'password' => 'aLphAnumeric123update',
            'email' => 'emailupdate@test.com',
        ];
    }

    /** @test */
    public function test_on_valid_data()
    {
        $this->withoutExceptionHandling();


        try {
            $response = $this->dispatch(new UpdateExistingCustomer($this->customer, $this->data));
            $this->assertTrue($response);
            $this->assertDatabaseHas('customers', $this->data);
        } catch (\Exception $e) {
            $this->assertNotInstanceOf(ValidationException::class, $e);
        }
    }

    /** @test */
    public function test_on_missing_data()
    {
        $this->withoutExceptionHandling();


        // test on missing attribute
        try {
            $response = $this->dispatch(new UpdateExistingCustomer($this->customer, []));
            $this->assertTrue($response);
        } catch (\Exception $e) {
            $this->assertNotInstanceOf(ValidationException::class, $e);
        }
    }

    /** @test */
    public function test_on_invalid_data()
    {
        $this->withoutExceptionHandling();

        $invalid_field_name = 'email';


        // test on invalid email
        $data = $this->data;
        $data[$invalid_field_name] = 'email';

        try {
            $response = $this->dispatch(new UpdateExistingCustomer($this->customer, $data));
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
