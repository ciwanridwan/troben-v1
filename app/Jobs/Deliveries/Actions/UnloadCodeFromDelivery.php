<?php

namespace App\Jobs\Deliveries\Actions;

use App\Models\Code;
use App\Models\CodeLogable;
use App\Models\Packages\Item;
use Illuminate\Validation\Rule;
use App\Models\Packages\Package;
use App\Models\Deliveries\Delivery;
use App\Models\Deliveries\Deliverable;
use Illuminate\Support\Facades\Validator;
use App\Events\Deliveries\Transit\WarehouseUnloadedPackage;
use App\Models\Partners\Pivot\UserablePivot;
use Illuminate\Database\Eloquent\Collection;

class UnloadCodeFromDelivery
{
    public Delivery $delivery;

    private Collection $codes;
    private Code $code;
    private ?string $role;

    /**
     * @var mixed
     */
    private ?string $status;

    private array $inputs;


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

        $this->inputs = Validator::make($inputs, [
            'code' => [
                'required',
                Rule::exists('codes', 'content')->whereIn('codeable_type', [
                    Package::class,
                    Item::class,
                ]),
            ],
            'status' => ['nullable', Rule::in(Deliverable::getStatuses())],
            'role' => ['nullable', Rule::in(array_merge(UserablePivot::getAvailableRoles(),[CodeLogable::STATUS_WAREHOUSE_UNLOAD]))],
        ])->validate();

        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->codes = Code::query()->whereIn('content', is_array($inputs['code']) ? $inputs['code'] : [$inputs['code']])->with('codeable')->get();

        $this->status = $this->inputs['status'];
        $this->role = $this->inputs['role'] ?? null;
    }

    public function handle(): void
    {
        $this->codes->each(function (Code $code) {
            $this->unloadFromDelivery($code);
        });

        return;
    }
    public function unloadFromDelivery(Code $code)
    {
        $this->code = $code;

        /** @var Package $package */
        $package = $this->code->codeable instanceof Package ? $this->code->codeable : $this->code->codeable->package;

        if ($this->code->codeable instanceof Item && $this->status) {
            $this->delivery->item_codes()->updateExistingPivot($this->code->id, [
                'status' => $this->status,
                'is_onboard' => Deliverable::isShouldOnBoard($this->status),
            ]);
            $this->delivery->packages()->updateExistingPivot($package->id, [
                'status' => $this->status,
                'is_onboard' => Deliverable::isShouldOnBoard($this->status),
            ]);

            // /** @var Code $code */
            // $code = $this->delivery->item_codes()->find($this->code->id);
        }
        event(new WarehouseUnloadedPackage($this->delivery, $package, $this->role));
    }
}
