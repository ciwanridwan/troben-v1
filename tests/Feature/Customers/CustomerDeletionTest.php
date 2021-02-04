<?php

namespace Tests\Feature\Customers;

use Tests\TestCase;
use App\Models\Customers\Customer;
use App\Jobs\Customers\CreateNewCustomer;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Jobs\Customers\DeleteExistingCustomer;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CustomerDeletionTest extends TestCase
{
    use  RefreshDatabase, DispatchesJobs;

    private $customer;
    public function setUp(): void
    {
        parent::setUp();
        $data = [
            'name' => 'username test',
            'phone' => '08512345679',
            'password' => 'aLphAnumeric123',
            'email' => 'email@test.com',
        ];
        $job = new CreateNewCustomer($data);
        // create new Customer on Refresh Database
        $this->dispatch($job);

        $this->customer = $job->customer;
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_on_valid_data()
    {
        $response = $this->dispatch(new DeleteExistingCustomer($this->customer));
        $this->assertTrue($response);
        $this->assertDatabaseMissing('customers', $this->customer->toArray());
    }
}
