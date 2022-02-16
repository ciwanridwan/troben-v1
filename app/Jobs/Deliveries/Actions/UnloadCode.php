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

class UnloadCode
{
    /**
     * @var mixed
     */
    public ?string $status;
    private Collection $codes;
    private Code $code;
    private ?string $role;

    private array $inputs;


    /**
     * ProcessFromCodeToDelivery constructor.
     *
     * @param array $inputs
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __construct(array $inputs = [])
    {
        $this->inputs = Validator::make($inputs, [
            'codes' => [
                'required',
                Rule::exists('codes', 'content')->whereIn('codeable_type', [
                    Package::class,
                    Item::class,
                ]),
            ],
            'status' => ['nullable', Rule::in(Deliverable::getStatuses())],
            'role' => ['nullable', Rule::in(array_merge(UserablePivot::getAvailableRoles(), [CodeLogable::STATUS_WAREHOUSE_UNLOAD]))],
        ])->validate();

        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->codes = Code::query()->whereIn('content', is_array($inputs['codes']) ? $inputs['codes'] : [$inputs['codes']])->with('codeable')->get();
        $this->status = $this->inputs['status'];
        $this->role = $this->inputs['role'] ?? null;
    }

    public function handle(): void
    {
        $this->codes->each(function (Code $code) {
            $deliveries = Deliverable::select('delivery_id')
                ->where('deliverable_type', 'App\Models\Code')
                ->where('status', 'load_by_driver')
                ->where('deliverable_id', $code->id)
                ->first();
            if ($deliveries == null) {
                $this->status = 'fail';
                return;
            }
            $delivery = Delivery::find($deliveries->delivery_id);
            $this->unloadFromDelivery($code, $delivery);
        });
        return;
    }
    public function unloadFromDelivery(Code $code, Delivery $delivery)
    {
        $this->code = $code;

        /** @var Package $package */
        $package = $this->code->codeable instanceof Package ? $this->code->codeable : $this->code->codeable->package;

        if ($this->code->codeable instanceof Item && $this->status) {
            $delivery->item_codes()->updateExistingPivot($this->code->id, [
                'status' => $this->status,
                'is_onboard' => Deliverable::isShouldOnBoard($this->status),
            ]);
            $delivery->packages()->updateExistingPivot($package->id, [
                'status' => $this->status,
                'is_onboard' => Deliverable::isShouldOnBoard($this->status),
            ]);

            // /** @var Code $code */
            // $code = $delivery->item_codes()->find($this->code->id);
        }
        event(new WarehouseUnloadedPackage($delivery, $package, $this->role));
    }
}
