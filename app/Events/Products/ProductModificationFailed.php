<?php

namespace App\Events\Products;

use App\Models\Products\Product;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProductModificationFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Product instance.
     *
     * @var \App\Models\Products\Product
     */
    public Product $product;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\Products\Product $product
     */
    public function __construct(Product $product)
    {
        $this->product = $product;
    }
}
