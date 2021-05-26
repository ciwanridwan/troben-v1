<?php

namespace App\Jobs\Deliveries\Actions;

use App\Models\Code;
use App\Models\Packages\Item;
use Illuminate\Validation\Rule;
use App\Models\Packages\Package;
use App\Models\Deliveries\Delivery;
use App\Models\Deliveries\Deliverable;
use Illuminate\Support\Facades\Validator;
use App\Events\Packages\PackageAttachedToDelivery;
use App\Events\Deliveries\Deliverable\DeliverableItemCodeUpdate;
use Illuminate\Database\Eloquent\Collection;

class ProcessFromCodeToDelivery
{
    private Delivery $delivery;

    private Collection $codes;
    private Code $code;
    /**
     * @var mixed
     */
    private ?string $status;

    /**
     * ProcessFromCodeToDelivery constructor.
     *
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
                ]),
            ],
            'status' => ['nullable', Rule::in(Deliverable::getStatuses())],
        ])->validate();

        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->codes = Code::query()->whereIn('content', is_array($inputs['code']) ? $inputs['code'] : [$inputs['code']])->with('codeable')->get();

        $this->status = $inputs['status'];
    }

    public function handle(): void
    {
        if ($this->delivery->status === Delivery::STATUS_WAITING_ASSIGN_PACKAGE && $this->delivery->userable_id === null) {
            $this->delivery->setAttribute('status', Delivery::STATUS_WAITING_ASSIGN_TRANSPORTER);
            $this->delivery->save();
        }


        $this->codes->each(function (Code $code) {
            $this->code = $code;

            /** @var Package $package */
            $package = $this->code->codeable instanceof Package ? $this->code->codeable : $this->code->codeable->package;


            if ($this->delivery->packages()->where('id', $package->id)->doesntExist()) {


                $this->delivery->packages()->attach($package);

                $itemCodes = $package->items->pluck('codes')->flatten(1);

                $this->delivery->item_codes()->syncWithoutDetaching($itemCodes->map->id->toArray());

                event(new PackageAttachedToDelivery($package, $this->delivery));
            }

            if ($this->code->codeable instanceof Item && $this->status) {

                $this->delivery->item_codes()->updateExistingPivot($this->code->id, [
                    'status' => $this->status,
                    'is_onboard' => Deliverable::isShouldOnBoard($this->status),
                ]);

                /** @var Code $code */
                $code = $this->delivery->item_codes()->find($this->code->id);

                event(new DeliverableItemCodeUpdate($code->pivot));
            }
        });
    }
}