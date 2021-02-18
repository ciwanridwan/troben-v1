<?php

namespace App\Jobs\Products;

use App\Events\Products\NewProductCreated;
use App\Models\Products\Product;
use Illuminate\Bus\Batchable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Validator;

class CreateNewProduct
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
    protected array $attributes;

    /**
     * CreateNewProduct constructor.
     *
     * @param array $inputs
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __construct($inputs = [])
    {
        $this->product = new Product();
        $this->attributes = Validator::make($inputs, [
            'name' => ['required','string','max:255','unique:products,name'],
            'description' => ['nullable','string','max:255'],
            'is_enabled' => ['required'],
        ])->validate();
    }

    /**
     * Handle job creating product.
     *
     * @return bool
     */
    public function handle(): bool
    {
        $this->product->fill($this->attributes);

        if ($this->product->save()) {
            event(new NewProductCreated($this->product));
        }

        return $this->product->exists;
    }
}
