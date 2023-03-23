<?php

namespace App\Jobs\Packages\Motobikes;

use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Geo\Regency;
use App\Models\Packages\Item;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Models\Packages\Package;
use App\Models\Partners\Transporter;
use App\Casts\Package\Items\Handling;
use App\Events\Packages\WalkinPackageBikeCreated;
use App\Models\Packages\MotorBike;
use Illuminate\Support\Facades\Validator;

class CreateWalkinOrderTypeBike
{
    use Dispatchable;

    public const MIN_TOL = .3;

    /**
     * Package instance.
     *
     * @var \App\Models\Packages\Package
     */
    public Package $package;
    /**
     * Package attributes.
     *
     * @var array
     */
    protected array $attributes;

    /**
     * Package items array.
     *
     * @var array
     */
    protected array $items;


    /**
     * Package bikes array.
     *
     * @var array
     */
    protected array $bikes;

    /**
     * Item separation flag.
     *
     * @var bool
     */
    protected bool $isSeparate;

    /**
     * CreateNewPackage constructor.
     *
     * @param array $inputs
     * @param array $items
     * @param bool  $isSeparate
     *
     * @throws \Illuminate\Validation\ValidationException
     */

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $inputs, array $items, bool $isSeparate = false, array $bikes)
    {
        $this->attributes = Validator::make($inputs, [
            'customer_id' => ['required', 'exists:customers,id'],
            'service_code' => ['required', 'exists:services,code'],
            'transporter_type' => ['nullable', Rule::in(Transporter::getAvailableTypes())],
            'sender_name' => ['required'],
            'sender_phone' => ['required'],
            'sender_address' => ['nullable'],
            'sender_way_point' => ['nullable'],
            'sender_latitude' => ['nullable'],
            'sender_longitude' => ['nullable'],
            'partner_code' => ['required'],

            'receiver_name' => ['required'],
            'receiver_phone' => ['required'],
            'receiver_address' => ['required'],
            'receiver_way_point' => ['nullable'],
            'receiver_latitude' => ['nullable'],
            'receiver_longitude' => ['nullable'],

            'handling' => ['nullable', 'array'],
            'handling.*' => ['string', Rule::in(Handling::TYPE_WOOD)],
            'origin_regency_id' => ['required', 'exists:geo_regencies,id'],
            'destination_regency_id' => ['required', 'exists:geo_regencies,id'],
            'destination_district_id' => ['required', 'exists:geo_districts,id'],
            'destination_sub_district_id' => ['required', 'exists:geo_sub_districts,id'],
        ])->validate();
        Log::info('validate package success', [$this->attributes['sender_name']]);

        $this->items = Validator::make($items, [
            'qty' => ['nullable', 'numeric'],
            'name' => 'required',
            'desc' => 'nullable',
            'is_insured' => ['nullable', 'boolean'],
            'price' => ['required_if:*.is_insured,true', 'numeric'],
            'handling' => ['nullable', 'array'],
            'handling.*' => ['string', Rule::in(Handling::TYPE_WOOD)],
            'height' => ['required_if:*.handling.*,wood', 'numeric'],
            'length' => ['required_if:*.handling.*,wood', 'numeric'],
            'width' => ['required_if:*.handling.*,wood', 'numeric'],
            'weight' => ['nullable', 'numeric'],
        ])->validate();
        Log::info('validate package items success', [$this->attributes['sender_name']]);

        $this->bikes = Validator::make($bikes, [
            'type' => ['required', 'in:kopling,gigi,matic'],
            'merk' => ['required'],
            'cc' => ['required', 'numeric'],
            'years' => ['required', 'numeric'],
            'package_id' => ['nullable', 'exists:packages,id'],
            'package_item_id' => ['nullable', 'exists:package_items,id']
        ])->validate();
        Log::info('validate bike success', [$this->attributes['sender_name']]);

        $this->item = new Item();
        $this->isSeparate = $isSeparate;
        $this->package = new Package();
        $this->motoBike = new MotorBike();

        Log::info('prepared finished. ', [$this->attributes['sender_name']]);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('Running job. ', [$this->attributes['sender_name']]);
        if (is_null($this->attributes['sender_address'])) {
            /** @var Regency $regency */
            $regency = Regency::query()->find($this->attributes['origin_regency_id']);
            $this->attributes['sender_address'] = $regency->name.', '.$regency->province->name;
        }

        $this->package->fill($this->attributes);
        $this->package->is_separate_item = $this->isSeparate;
        $this->package->created_by = auth()->user()->id;
        $this->package->save();
        Log::info('trying insert package to db. ', [$this->attributes['sender_name']]);

        if ($this->package->exists) {
            $this->item->fill($this->items);
            $this->item->package_id = $this->package->id;
            $this->item->qty = 1;
            $this->item->weight = 0;
            $this->item->save();
            Log::info('after saving package items success. ', [$this->attributes['sender_name']]);

            $this->motoBike->fill($this->bikes);
            $this->motoBike->package_id = $this->package->id;
            $this->motoBike->package_item_id = $this->item->id;
            $this->motoBike->save();
            Log::info('Saving package bike success. ', [$this->attributes['sender_name']]);

            event(new WalkinPackageBikeCreated($this->package, $this->attributes['partner_code']));
            Log::info('triggering event. ', [$this->attributes['sender_name']]);
        }
        return $this->package->exists;
    }
}
