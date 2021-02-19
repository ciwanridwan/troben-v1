<?php

namespace App\Jobs\Products;

use Illuminate\Bus\Batchable;
use App\Models\Products\Product;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Validator;
use App\Events\Products\NewProductCreated;
use Illuminate\Foundation\Bus\Dispatchable;

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
