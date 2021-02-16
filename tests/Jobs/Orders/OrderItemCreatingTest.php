<?php

namespace Tests\Jobs\Orders;

use Tests\TestCase;
use App\Http\Response;
use App\Models\Orders\Order;
use App\Models\Customers\Customer;
use App\Events\Orders\OrderCreated;
use App\Jobs\Orders\CreateNewOrder;
use Illuminate\Support\Facades\Event;
use App\Jobs\Orders\CreateNewOrderItem;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderItemCreatingTest extends TestCase
{
    use DispatchesJobs, RefreshDatabase;
    protected $order;
    public function setUp(): void
    {
        parent::setUp();
        $customer = Customer::factory(1)->create()->first();
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
        $job = new CreateNewOrder($customer, $data);
        $this->dispatch($job);
        $this->order = $job->order;
    }
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_order_item()
    {
        $this->withoutExceptionHandling();
        // test with single data
        Event::fake();
        $data = [
            'name' => 'test',
            'qty' => 1,
            'desc' => 'test',
        ];
        $job = new CreateNewOrderItem($this->order, $data);
        $response = $this->dispatch($job);
        $this->assertTrue($response);
        Event::assertNotDispatched(OrderCreated::class);

        // test with multiple data
        Event::fake();
        $data = [
            'items' => [
                [
                    'name' => 'test',
                    'qty' => 1,
                    'desc' => 'test',
                ],
            ],
            'name' => 'test',
            'qty' => 1,
            'desc' => 'test',
        ];
        $job = new CreateNewOrderItem($this->order, $data);
        $response = $this->dispatch($job);
        $this->assertTrue($response);
        Event::assertNotDispatched(OrderCreated::class);
    }

    public function test_invalid_data()
    {
        $this->expectException(ValidationException::class);
        // test with invalid data
        $data = [
            'name' => '',
            'qty' => 'av',
            'desc' => 'test',
        ];
        $job = new CreateNewOrderItem($this->order, $data);
        $response = $this->dispatch($job);
        $this->assertResponseWithCode($response, Response::RC_INVALID_DATA);
    }

    public function test_missing_data()
    {
        $this->expectException(ValidationException::class);
        // test with missing data
        $job = new CreateNewOrderItem($this->order, []);
        $response = $this->dispatch($job);
        $this->assertResponseWithCode($response, Response::RC_INVALID_DATA);
    }
}
