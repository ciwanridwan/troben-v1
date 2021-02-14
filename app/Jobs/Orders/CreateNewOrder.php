<?php

namespace App\Jobs\Orders;

use App\Models\Order;
use Illuminate\Validation\Rule;
use App\Models\Customers\Customer;
use App\Events\Orders\OrderCreated;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateNewOrder
{
    use Dispatchable;

    /**
     * @var array
     */
    public array $attributes;

    /**
     * @var Customer
     */
    public Customer $customer;

    /**
     * @var Order
     */
    public Order $order;

    /**
     * @param Customer $customer
     * @param array $inputs
     */
    public function __construct(Customer $customer, $inputs = [])
    {
        $this->customer = $customer;
        $this->attributes = Validator::make($inputs, [
            'est_payment' => ['required'],
            'total_payment' => ['required'],
            'payment_status' => ['nullable', 'required'],
            'payment_ref_id' => ['nullable', 'required'],
            'est_weight' => ['required', 'numeric'],
            'est_height' => ['required', 'numeric'],
            'est_length' => ['required', 'numeric'],
            'est_width' => ['required', 'numeric'],
            'status' => ['required', Rule::in(Order::STATUS)],
        ])->validate();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // set customer as sender
        $this->attributes['sender_name'] = $this->customer->name;
        $this->attributes['sender_phone'] = $this->customer->phone;

        $this->order = new Order();
        $this->order->fill($this->attributes);

        if ($this->order->save()) {
            event(new OrderCreated($this->order));
        }

        return $this->order->exists;
    }
}
