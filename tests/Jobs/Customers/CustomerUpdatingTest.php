<?php

namespace Tests\Jobs\Customers;

use Tests\TestCase;
use Illuminate\Support\Arr;
use App\Models\Customers\Customer;
use Database\Seeders\CustomersTableSeeder;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Jobs\Customers\UpdateExistingCustomer;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CustomerUpdatingTest extends TestCase
{
    use RefreshDatabase, DispatchesJobs;

    protected array $updateData = [
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
        $subject = $this->getTestSubject();

        $job = new UpdateExistingCustomer($subject, $this->updateData);
        $this->assertTrue($this->dispatch($job));

        $this->assertDatabaseHas('customers', Arr::only($this->updateData, ['name', 'email']));
    }

    /** @test */
    public function test_on_missing_data()
    {
        $subject = $this->getTestSubject();

        $this->expectException(ValidationException::class);

        $this->dispatch(new UpdateExistingCustomer($subject, [
            'name' => '',
        ]));
    }

    /** @test */
    public function test_on_invalid_data()
    {
        try {
            $response = $this->dispatch(new UpdateExistingCustomer($this->getTestSubject(), Arr::except($this->updateData, 'email')));
            $this->assertTrue($response);
        } catch (\Exception $e) {
            $this->assertInstanceOf(ValidationException::class, $e);

            /** @var \Illuminate\Validation\ValidationException $e */
            $this->assertArrayHasKey('email', $e->errors());

            /** @var \Illuminate\Validation\ValidationException $e */
            collect(Arr::except($this->updateData, 'email'))
                ->each(fn ($v, $k) => $this->assertArrayNotHasKey($k, $e->errors()));
        }
    }

    /***
     * Get Customer Test Subject.
     *
     * @return \App\Models\Customers\Customer|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object
     */
    protected function getTestSubject()
    {
        $this->seed(CustomersTableSeeder::class);

        return Customer::query()->inRandomOrder()->first();
    }
}
