<?php

namespace App\Jobs\Products;

use App\Events\Products\ProductDeleted;
use App\Models\Products\Product;
use Illuminate\Bus\Batchable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteExistingProduct
{
    use Dispatchable, InteractsWithQueue, SerializesModels, Batchable;

    /**
     * Product instance.
     *
     * @var \App\Models\Products\Product
     */
    public Product $product;

    /**
     * Soft delete flag.
     *
     * @var bool
     */
    public bool $softDelete;

    /**
     * DeleteExistingProduct constructor.
     *
     * @param \App\Models\Products\Product $product
     * @param bool                         $force
     */
    public function __construct(Product $product, $force = false)
    {
        $this->product = $product;
        $this->softDelete = ! $force;
    }

    /**
     * Execute the job.
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function handle(): bool
    {
        (bool) $result = $this->softDelete ? $this->product->delete() : $this->product->forceDelete();

        event(new ProductDeleted($this->product));

        return $result;
    }
}
