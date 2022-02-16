<?php

namespace App\Jobs\Deliveries\Actions;

use App\Events\CodeScanned;
use App\Models\Code;
use App\Models\CodeLogable;
use App\Models\Packages\Item;
use Illuminate\Validation\Rule;
use App\Models\Packages\Package;
use App\Models\Deliveries\Delivery;
use App\Models\Deliveries\Deliverable;
use Illuminate\Support\Facades\Validator;
use App\Events\Packages\PackageAttachedToDelivery;
use App\Events\Deliveries\Deliverable\DeliverableItemCodeUpdate;
use App\Models\Partners\Pivot\UserablePivot;
use Illuminate\Database\Eloquent\Collection;

class ProcessFromCode
{
    /**
     * @var mixed
     */
    public ?string $status;
    private Collection $codes;
    private Code $code;
    private ?string $role;
    private bool $logging;
    /**
     * ProcessFromCodeToDelivery constructor.
     *
     * @param \App\Models\Deliveries\Delivery $delivery
     * @param array $inputs
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __construct(array $inputs = [])
    {
        Validator::make($inputs, [
            'codes' => [
                'required',
                Rule::exists('codes', 'content')->whereIn('codeable_type', [
                    Package::class,
                    Item::class,
                ]),
            ],
            'status' => ['nullable', Rule::in(Deliverable::getStatuses())],
            'role' => ['nullable', Rule::in(array_merge(UserablePivot::getAvailableRoles(), [
                CodeLogable::STATUS_DRIVER_LOAD,
                CodeLogable::STATUS_DRIVER_DOORING_LOAD
            ]))],
        ])->validate();

        $this->codes = Code::query()->whereIn('content', is_array($inputs['codes']) ? $inputs['codes'] : [$inputs['codes']])->with('codeable')->get();

        $this->status = $inputs['status'];
        $this->role = $inputs['role'];
    }

    public function handle(): void
    {
        $this->codes->each(function (Code $code) {
            $deliveries = Deliverable::select('delivery_id')
                ->where('deliverable_type', 'App\Models\Code')
                ->where('status', 'prepared_by_origin_warehouse')
                ->where('deliverable_id', $code->id)
                ->first();
            if ($deliveries == null) {
                $this->status = 'fail';
                return;
            }
            $delivery = Delivery::find($deliveries->delivery_id);

            if ($delivery->status === Delivery::STATUS_WAITING_ASSIGN_PACKAGE && $delivery->userable_id === null) {
                $delivery->setAttribute('status', Delivery::STATUS_WAITING_ASSIGN_TRANSPORTER);
                $delivery->save();
            }

            $this->assignToDelivery($code, $delivery);
        });
    }

    public function assignToDelivery(Code $code, Delivery $delivery)
    {
        $this->code = $code;

        /** @var Package $package */
        $package = $this->code->codeable instanceof Package ? $this->code->codeable : $this->code->codeable->package;

        $this->checkAndAttachPackageToDelivery($package, $delivery);

        if ($this->code->codeable instanceof Item && $this->status) {
            $delivery->item_codes()->syncWithoutDetaching([$code->id]);

            $delivery->item_codes()->updateExistingPivot($this->code->id, [
                'status' => $this->status,
                'is_onboard' => Deliverable::isShouldOnBoard($this->status),
            ]);

            /** @var Code $code */
            $code = $delivery->item_codes()->find($this->code->id);

            event(new DeliverableItemCodeUpdate($code->pivot));
            event(new CodeScanned($delivery, $this->code, $this->role));
        }
    }
    public function checkAndAttachPackageToDelivery($package, $delivery)
    {
        $this->mustLogging($package, $delivery);
        if ($delivery->packages()->where('id', $package->id)->doesntExist()) {
            $delivery->packages()->attach($package);

            if ($this->status) {
                $delivery->packages()->updateExistingPivot($package->id, [
                    'status' => $this->status,
                    'is_onboard' => Deliverable::isShouldOnBoard($this->status),
                ]);
            }

            // $itemCodes = $package->items->pluck('codes')->flatten(1);

            // $delivery->item_codes()->syncWithoutDetaching($itemCodes->map->id->toArray());

            event(new PackageAttachedToDelivery($package, $delivery));
        } else {
            if ($this->status) {
                $delivery->packages()->updateExistingPivot($package->id, [
                    'status' => $this->status,
                    'is_onboard' => Deliverable::isShouldOnBoard($this->status),
                ]);
            }
        }
        if ($this->logging || $this->role === CodeLogable::STATUS_WAREHOUSE) {
            event(new CodeScanned($delivery, $package->code, $this->role));
        }
    }

    private function mustLogging(Package $package, Delivery $delivery): void
    {
        $this->logging = $package->deliveries()->where('type', Delivery::TYPE_TRANSIT)->count() > 1
            || $delivery->type === Delivery::TYPE_DOORING;
    }
}
