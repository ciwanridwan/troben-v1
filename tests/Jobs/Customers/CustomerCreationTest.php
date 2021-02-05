<?php

namespace Tests\Jobs\Customers;

use Tests\TestCase;
use Illuminate\Support\Arr;
use App\Models\Customers\Customer;
use Illuminate\Support\Facades\Event;
use App\Jobs\Customers\CreateNewCustomer;
use App\Events\Customers\NewCustomerCreated;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CustomerCreationTest extends TestCase
{
    use RefreshDatabase, DispatchesJobs;

    private array $data = [
        'name' => 'username test',
        'phone' => '08512345679',
        'password' => 'aLphAnumeric123',
        'email' => 'email@test.com',
    ];

    /**
     * @test
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function test_on_valid_data()
    {
        Event::fake();

        $job = new CreateNewCustomer($this->data);

        $this->assertTrue($this->dispatch($job));

        $this->assertInstanceOf(Customer::class, $job->customer);

        $this->assertTrue($job->customer->exists);

        $this->assertDatabaseHas('customers', Arr::only($this->data, ['email']));

        Event::assertDispatched(NewCustomerCreated::class);
    }

    /** @test */
    public function test_on_missing_data()
    {
        $missing_field_name = 'name';
        $this->withoutExceptionHandling();
        $data = Arr::except($this->data, $missing_field_name);

        // test on missing attribute
        try {
            $response = $this->dispatch(new CreateNewCustomer($data));
            $this->assertTrue($response);
            $this->assertDatabaseHas('customers', Arr::only($this->data, ['username', 'email']));
        } catch (\Exception $e) {
            $this->assertInstanceOf(ValidationException::class, $e);
            $this->assertArrayHasKey($missing_field_name, $e->errors());
            foreach ($data as $key => $value) {
                $this->assertArrayNotHasKey($key, $e->errors());
            }
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
            $response = $this->dispatch(new CreateNewCustomer($data));
            $this->assertTrue($response);
            $this->assertDatabaseHas('customers', Arr::only($this->data, ['username', 'email']));
        } catch (\Exception $e) {
            $this->assertInstanceOf(ValidationException::class, $e);
            $this->assertArrayHasKey($invalid_field_name, $e->errors());
            foreach (Arr::except($data, $invalid_field_name) as $key => $value) {
                $this->assertArrayNotHasKey($key, $e->errors());
            }
        }
    }
}
