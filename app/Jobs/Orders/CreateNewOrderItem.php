<?php

namespace App\Jobs\Orders;

use App\Models\Orders\Order;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateNewOrderItem
{
    use Dispatchable;

    /**
     * @var Order
     */
    public Order $order;

    public OrderItem


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Order $order, $inputs)
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
    }
}
