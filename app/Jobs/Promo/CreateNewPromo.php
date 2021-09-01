<?php

namespace App\Jobs\Promo;

use App\Models\Packages\Package;
use App\Models\Promo;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreateNewPromo
{
    use Dispatchable;

    /**
     * Package instance.
     *
     * @var Promo
     */
    public Promo $promo;

    /**
     * Promo attributes.
     *
     * @var array
     */
    protected array $attributes;

    /**
     * Item separation flag.
     *
     * @var bool
     */
    protected bool $isSeparate;

    /**
     * CreateNewPromo constructor.
     * @throws ValidationException
     */
    public function __construct($inputs = [])
    {
        $this->attributes = Validator::make($inputs, [
            'title' => ['nullable'],
            'content' => ['nullable'],
            'description' => ['nullable'],
            'type' => ['nullable'],
            'is_active' => ['nullable'],
            'source' => ['nullable'],
            'author' => ['nullable'],
            'image' => ['nullable'],
        ])->validate();

        $this->promo = new Promo();
    }

    /**
     * @return bool
     */
    public function handle() : bool
    {
        $this->promo->fill($this->attributes);
        $this->promo->save();

        return $this->promo->exists;
    }
}
