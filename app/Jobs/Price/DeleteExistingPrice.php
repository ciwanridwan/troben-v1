<?php

namespace App\Jobs\Price;

use App\Models\Price;
use Illuminate\Bus\Batchable;
use App\Events\Price\PriceDeleted;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class DeleteExistingPrice
{
    use Dispatchable, InteractsWithQueue, SerializesModels, Batchable;

    /**
     * Price instance.
     *
     * @var \App\Models\Price
     */
    public Price $price;

    /**
     * Soft delete flag.
     *
     * @var bool
     */
    public bool $softDelete;

    /**
     * DeleteExistingPrice constructor.
     *
     * @param \App\Models\Price $price
     * @param bool              $force
     *
     * @return void
     */
    public function __construct(Price $price, $force = false)
    {
        $this->price = $price;
        $this->softDelete = ! $force;
    }

    /**
     * Execute DeleteExistingPrice job.
     *
     * @return void
     */
    public function handle(): bool
    {
        (bool) $result = $this->softDelete ? $this->price->delete() : $this->price->forceDelete();

        event(new PriceDeleted($this->price));

        return $result;
    }
}
