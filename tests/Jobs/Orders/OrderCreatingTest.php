<?php

namespace Tests\Jobs\Orders;

use Tests\TestCase;
use App\Models\Order;
use App\Models\Customers\Customer;
use App\Jobs\Orders\CreateNewOrder;
use Carbon\Carbon;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderCreatingTest extends TestCase
{
    use DispatchesJobs, RefreshDatabase;

    /**
     * @var Customer
     */
    protected Customer $customer;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->customer = Customer::first();
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_on_valid_data()
    {
        // on valid data
        $data = [
            'est_payment' => '1',
            'total_payment' => '1',
            'payment_status' => '1',
            'payment_ref_id' => '1',
            'est_weight' => 1,
            'est_height' => 1,
            'est_length' => 1,
            'est_width' => 1,
            'status' => Order::STATUS_DOOR,
        ];
        $job = new CreateNewOrder($this->customer, $data);
        $response = $this->dispatch($job);
        $order = $job->order;
        dd($order->toArray(), (new Order())->generateBarcode());
        $this->assertTrue($response);
        $this->assertDatabaseHas('orders', $order->only(['id', 'barcode']));
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_on_invalid_data()
    {
        $this->expectException(ValidationException::class);
        // on valid data
        $customer = Customer::first();
        $data = [
            'est_payment' => null,
            'total_payment' => null,
            'payment_status' => null,
            'payment_ref_id' => null,
            'est_weight' => null,
            'est_height' => null,
            'est_length' => null,
            'est_width' => null,
            'status' => 'ORDERGAN',
        ];
        $job = new CreateNewOrder($customer, $data);
        $response = $this->dispatch($job);
        $order = $job->order;
    }
}
