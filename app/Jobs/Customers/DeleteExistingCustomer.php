<?php

namespace App\Jobs\Customers;

use Illuminate\Bus\Batchable;
use App\Models\Customers\Customer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\Customers\CustomerDeleted;
use Illuminate\Foundation\Bus\Dispatchable;

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
     * Soft Delete flag.
     *
     * @var bool
     */
    public bool $softDelete;

    /**
     * DeleteExistingCustomer constructor.
     *
     * @param \App\Models\Customers\Customer $customer
     * @param bool                           $force
     */
    public function __construct(Customer $customer, $force = false)
    {
        $this->customer = $customer;
        $this->softDelete = ! $force;
    }

    /**
     * Handle the job.
     *
     * @return bool
     * @throws \Exception
     */
    public function handle(): bool
    {
        (bool) $result = $this->softDelete ? $this->customer->delete() : $this->customer->forceDelete();

        event(new CustomerDeleted($this->customer));

        return $result;
    }
}
