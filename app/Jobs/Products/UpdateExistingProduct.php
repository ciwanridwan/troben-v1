<?php

namespace App\Jobs\Products;

use App\Events\Products\ProductModificationFailed;
use App\Events\Products\ProductModified;
use App\Models\Products\Product;
use Illuminate\Bus\Batchable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Validator;

class UpdateExistingProduct
{
    use Dispatchable, InteractsWithQueue, SerializesModels, Batchable;

    /**
     * Product instance.
     *
     * @var \App\Models\Products\Product
     */
    public Product $product;

    /**
     * Filtered attributes.
     *
     * @var array
     */
    public array $attributes;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Product $product, $inputs = [])
    {
        $this->product = $product;
        $this->attributes = Validator::make($inputs, [
            'name' => ['filled', "unique:products,name,$product->id,id", 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_enabled' => ['filled']
        ])->validate();
    }

    /**
     * Update Existing Product Job.
     *
     * @return void
     */
    public function handle(): bool
    {
        collect($this->attributes)->each(fn ($v, $k) => $this->product->{$k} = $v);

        if ($this->product->isDirty() && $this->product->save()) {
            event(new ProductModified($this->product));
        } else {
            event(new ProductModificationFailed($this->product));
        }

        return $this->product->exists;
    }
}
