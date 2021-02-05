<?php

namespace Tests\Jobs\Customers;

use Tests\TestCase;
use Illuminate\Support\Arr;
use App\Models\Customers\Customer;
use Database\Seeders\CustomersTableSeeder;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Jobs\Customers\DeleteExistingCustomer;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CustomerDeletionTest extends TestCase
{
    use  RefreshDatabase, DispatchesJobs;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_on_valid_data()
    {
        $this->seed(CustomersTableSeeder::class);

        /** @var \App\Models\Customers\Customer $customer */
        $customer = Customer::query()->inRandomOrder()->first();

        $job = new DeleteExistingCustomer($customer);
        $this->assertTrue($this->dispatch($job));

        $this->assertDatabaseMissing('customers', $customer->toArray());
        $this->assertSoftDeleted('customers', Arr::only($customer->toArray(), 'id'));
    }
}
