<?php

namespace App\Jobs\Promo;

use App\Casts\Package\Items\Handling;
use App\Models\Packages\Package;
use App\Models\Partners\Transporter;
use App\Models\Promo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
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
            'title' => ['required'],
            'content' => ['required'],
            'description' => ['nullable'],
            'type' => ['nullable'],
            'is_active' => ['nullable'],
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
