<?php

namespace App\Jobs\Deliveries\Actions;

use App\Events\CodeScanned;
use App\Events\Packages\PackagesAttachedToDelivery;
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
use App\Models\Partners\Partner;
use App\Models\Partners\Pivot\UserablePivot;
use Illuminate\Database\Eloquent\Collection;

class ProcessFromCodeToDelivery
{
    public Delivery $delivery;

    private Collection $codes;
    private Code $code;
    private ?string $role;
    private bool $logging;

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
            'role' => ['nullable', Rule::in(array_merge(UserablePivot::getAvailableRoles(), [
                CodeLogable::STATUS_DRIVER_LOAD,
                CodeLogable::STATUS_DRIVER_DOORING_LOAD
            ]))],
        ])->validate();

        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->codes = Code::query()->whereIn('content', is_array($inputs['code']) ? $inputs['code'] : [$inputs['code']])->with('codeable')->get();
        $this->package_codes = Code::query()->where('codeable_type', Package::class)->whereIn('content', is_array($inputs['code']) ? $inputs['code'] : [$inputs['code']])->with('codeable')->get();
        $this->status = $inputs['status'];
        $this->role = $inputs['role'];
    }

    public function handle(): void
    {
        $this->transitOfPackage();

        if ($this->delivery->status === Delivery::STATUS_WAITING_ASSIGN_PACKAGE && $this->delivery->userable_id === null) {
            $this->delivery->setAttribute('status', Delivery::STATUS_WAITING_ASSIGN_TRANSPORTER);
            $this->delivery->save();
        }

        $this->codes->each(function (Code $code) {
            $this->assignToDelivery($code);
        });

        event(new PackagesAttachedToDelivery($this->delivery));
    }

    public function assignToDelivery(Code $code)
    {
        $this->code = $code;

        /** @var Package $package */
        $package = $this->code->codeable instanceof Package ? $this->code->codeable : $this->code->codeable->package;

        $this->checkAndAttachPackageToDelivery($package);

        if ($this->code->codeable instanceof Item && $this->status) {
            $this->delivery->item_codes()->syncWithoutDetaching([$code->id]);

            $this->delivery->item_codes()->updateExistingPivot($this->code->id, [
                'status' => $this->status,
                'is_onboard' => Deliverable::isShouldOnBoard($this->status),
            ]);

            /** @var Code $code */
            $code = $this->delivery->item_codes()->find($this->code->id);

            event(new DeliverableItemCodeUpdate($code->pivot));
            event(new CodeScanned($this->delivery, $this->code, $this->role));
        }
    }

    public function checkAndAttachPackageToDelivery($package)
    {
        $this->mustLogging($package);
        if ($this->delivery->packages()->where('id', $package->id)->doesntExist()) {
            $this->delivery->packages()->attach($package);

            if ($this->status) {
                $this->delivery->packages()->updateExistingPivot($package->id, [
                    'status' => $this->status,
                    'is_onboard' => Deliverable::isShouldOnBoard($this->status),
                ]);
            }

            // $itemCodes = $package->items->pluck('codes')->flatten(1);

            // $this->delivery->item_codes()->syncWithoutDetaching($itemCodes->map->id->toArray());

            event(new PackageAttachedToDelivery($package, $this->delivery));
        } else {
            if ($this->status) {
                $this->delivery->packages()->updateExistingPivot($package->id, [
                    'status' => $this->status,
                    'is_onboard' => Deliverable::isShouldOnBoard($this->status),
                ]);
            }
        }
        if ($this->logging || $this->role === CodeLogable::STATUS_WAREHOUSE) {
            event(new CodeScanned($this->delivery, $package->code, $this->role));
        }
    }

    private function mustLogging(Package $package): void
    {
        $this->logging = $package->deliveries()->where('type', Delivery::TYPE_TRANSIT)->count() > 1
            || $this->delivery->type === Delivery::TYPE_DOORING;
    }

    /**
     * Set transit count of packages
     * @return int $transit_count
     * */
    private function setTransitCount($packageCode)
    {
        $this->code = $packageCode;
        $packages = $this->code->codeable instanceof Package ? $this->code->codeable : $this->code->codeable->package;

        if ($packages->transit_count == null || $packages->transit_count == 0) {
            $packages->transit_count = 1;
            $packages->save();
        } else {
            $packages->transit_count += 1;
            $packages->save();
        }
    }

    /**
     * List transit of package
     */
    private function transitOfPackage()
    {
        if ($this->delivery->type === Delivery::TYPE_DOORING && $this->delivery->status === Delivery::STATUS_WAITING_ASSIGN_PACKAGE) {
            $partner = $this->delivery->origin_partner()->first();
            if ($partner->type == Partner::TYPE_POOL) {
                $this->package_codes->each(function (Code $packageCode) {
                    $this->setTransitCount($packageCode);
                });
            }
        } elseif ($this->delivery->type === Delivery::TYPE_TRANSIT && $this->delivery->status === Delivery::STATUS_WAITING_ASSIGN_PACKAGE) {
            $partner = $this->delivery->origin_partner()->first();
            if ($partner->type == Partner::TYPE_POOL) {
                $this->package_codes->each(function (Code $packageCode) {
                    $this->setTransitCount($packageCode);
                });
            } else {
                $originPartner = $this->delivery->origin_partner()->first();
                $destinationPartner = $this->delivery->partner()->first();

                if ($originPartner->geo_regency_id !== $destinationPartner->geo_regency_id) {
                    $this->package_codes->each(function (Code $packageCode) {
                        $this->setTransitCount($packageCode);
                    });
                }
            }
        }
    }
}
