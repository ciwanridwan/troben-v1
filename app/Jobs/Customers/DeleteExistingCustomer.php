<?php

namespace App\Jobs\Customers;

use App\Events\Customers\CustomerDeleted;
use Illuminate\Bus\Batchable;
use App\Models\Customers\Customer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use phpDocumentor\Reflection\Types\Null_;

class DeleteExistingCustomer
{
    use Dispatchable, SerializesModels, InteractsWithQueue, Batchable;

    /**
     * Customer instance.
     *
     * @var \App\Models\Customers\Customer
     */
    public Customer $customer;

    /**
     * DeleteExistingCustomer constructor.
     *
     * @param \App\Models\Customers\Customer $customer
     */
    public function __construct(Customer $customer)
    {
        $this->customer = $customer;
    }

    /**
     * Handle the job.
     *
     * @return bool
     * @throws \Exception
     */
    public function handle(): bool
    {

        if ($this->customer->delete()) {
            // TODO:: fire success event.
            event(new CustomerDeleted);
        }

        return $this->customer->deleted_at !== null;
    }
}
