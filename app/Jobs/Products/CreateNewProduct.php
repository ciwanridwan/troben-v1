<?php

namespace App\Jobs\Products;

use Illuminate\Bus\Batchable;
use App\Models\Products\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Validator;
use App\Events\Products\NewProductCreated;
use Illuminate\Foundation\Bus\Dispatchable;
use Jalameta\Attachments\Concerns\AttachmentCreator;

class CreateNewProduct
{
    use Dispatchable, InteractsWithQueue, SerializesModels, Batchable, AttachmentCreator;

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
     * @var ?UploadedFile
     */
    public ?UploadedFile $file;

    /**
     * CreateNewProduct constructor.
     *
     * @param array $inputs
     * @param UploadedFile|null $file
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __construct($inputs = [], ?UploadedFile $file = null)
    {
        $this->file = $file;

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
        if (! is_null($this->file)) {
            $attachment = $this->create($this->file, [
                'title' => 'Logo ' . $this->attributes['name']
            ]);

            $this->attributes['logo'] = $attachment->getAttribute('uri');
        }

        $this->product->fill($this->attributes);

        if ($this->product->save()) {

//            if (isset($attachment)) {
//                $this->product->attachments()->attach($attachment->id);
//            }

            event(new NewProductCreated($this->product));
        }

        return $this->product->exists;
    }
}
