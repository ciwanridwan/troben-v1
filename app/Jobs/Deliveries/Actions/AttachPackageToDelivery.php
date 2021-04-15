<?php

namespace App\Jobs\Deliveries\Actions;

use App\Events\Packages\PackageAttachedToDelivery;
use App\Models\Code;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Item;
use App\Models\Packages\Package;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AttachPackageToDelivery
{
    private Delivery $delivery;

    private Code $code;

    /**
     * AttachPackageToDelivery constructor.
     * @param \App\Models\Deliveries\Delivery $delivery
     * @param array $inputs
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __construct(Delivery $delivery, array $inputs = [])
    {
        $this->delivery = $delivery;

        Validator::make($inputs, [
            'code' => [
                'required',
                Rule::exists('codes', 'content')->whereIn('codeable_type', [
                    Package::class,
                    Item::class,
                ])
            ],
        ])->validate();

        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->code = Code::query()->where('content', $inputs['code'])->with('codeable')->first();
    }

    public function handle()
    {
        if ($this->delivery->status === Delivery::STATUS_WAITING_ASSIGN_PACKAGE && $this->delivery->userable_id === null) {
            $this->delivery->setAttribute('status', Delivery::STATUS_WAITING_ASSIGN_TRANSPORTER);
            $this->delivery->save();
        }

        $package = $this->code->codeable instanceof Package ? $this->code->codeable : $this->code->codeable->package;

        $this->delivery->packages()->attach($this->delivery);

        $package->items->pluck('codes')->flatten(1)->each(fn(Code $code) => $this->delivery->item_codes()->attach($code));

        event(new PackageAttachedToDelivery($package, $this->delivery));
    }
}
