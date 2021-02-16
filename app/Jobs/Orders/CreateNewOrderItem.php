<?php

namespace App\Jobs\Orders;

use App\Models\Orders\Item;
use Illuminate\Support\Arr;
use App\Models\Orders\Order;
use App\Events\Orders\OrderItemCreated;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Database\Eloquent\Collection;

class CreateNewOrderItem
{
    use Dispatchable;

    /**
     * @var Order
     */
    public Order $order;

    /**
     * @var Item|Collection
     */
    public $order_item;

    public array $attributes;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Order $order, $inputs = [])
    {
        $this->order = $order;
        if (Arr::has($inputs, 'items')) {
            $this->attributes = Validator::make($inputs['items'], [
                '*.name' => ['required'],
                '*.qty' => ['required', 'numeric'],
                '*.desc' => ['required', 'required'],
            ])->validate();
        } else {
            $this->attributes = Validator::make($inputs, [
                'name' => ['required'],
                'qty' => ['required', 'numeric'],
                'desc' => ['required', 'required'],
            ])->validate();
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (array_key_first($this->attributes) === 0) {
            $this->order_item = $this->order->items()->createMany($this->attributes);
        } else {
            $this->order_item = $this->order->items()->create($this->attributes);
        }

        if ($this->order_item) {
            Event(new OrderItemCreated($this->order_item));
        }

        if ($this->order_item instanceof Collection) {
            foreach ($this->order_item as $item) {
                if (! $item->exists) {
                    return $item->exists;
                }
            }

            return true;
        } else {
            return $this->order_item->exists;
        }
    }
}
