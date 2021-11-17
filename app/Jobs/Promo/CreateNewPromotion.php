<?php

namespace App\Jobs\Promo;

use App\Models\Promotion;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreateNewPromotion
{
    use Dispatchable;

    /**
     * Package instance.
     *
     * @var Promotion
     */
    public Promotion $promotion;

    /**
     * Promotion attributes.
     *
     * @var array
     */
    protected array $attributes;

    /**
     * CreateNewPromotion constructor.
     * @param array $inputs
     * @throws ValidationException
     */
    public function __construct($inputs = [])
    {
        $this->attributes = Validator::make($inputs, [
            'title' => ['required'],
            'type' => ['nullable'],
            'terms_and_conditions' => ['required'],
            'min_payment' => ['required'],
            'max_payment' => ['required'],
            'min_weight' => ['required'],
            'max_weight' => ['required'],
            'start_date' => ['required'],
            'end_date' => ['required'],
        ])->validate();

        $this->promotion = new Promotion();
    }

    /**
     * @return bool
     */
    public function handle() : bool
    {
        $this->promotion->fill($this->attributes);
        $this->promotion->save();

        return $this->promotion->exists;
    }
}
