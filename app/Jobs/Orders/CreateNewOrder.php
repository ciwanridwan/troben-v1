<?php

namespace App\Jobs\Orders;

use App\Models\Orders\Order;
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
     * @param array    $inputs
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __construct(Customer $customer, array $inputs = [])
    {
        $this->customer = $customer;
        $this->attributes = Validator::make($inputs, [
            'total_payment' => ['required'],
            'payment_status' => ['nullable', 'required'],
            'payment_ref_id' => ['nullable', 'required'],
            'status' => ['required', Rule::in(Order::STATUS)],
        ])->validate();
    }

    /**
     * Execute the job.
     *
     * @return bool
     */
    public function handle(): bool
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
