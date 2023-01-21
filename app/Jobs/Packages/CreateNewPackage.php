<?php

namespace App\Jobs\Packages;

use App\Models\Geo\Regency;
use App\Models\Packages\Item;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Models\Packages\Package;
use App\Models\Partners\Transporter;
use App\Casts\Package\Items\Handling;
use App\Events\Packages\PackageCreated;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateNewPackage
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
    public function __construct(array $inputs, array $items, bool $isSeparate = false)
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
            'handling.*' => ['string', Rule::in(Handling::getTypes())],
            'origin_regency_id' => ['required', 'exists:geo_regencies,id'],
            'destination_regency_id' => ['required', 'exists:geo_regencies,id'],
            'destination_district_id' => ['required', 'exists:geo_districts,id'],
            'destination_sub_district_id' => ['required', 'exists:geo_sub_districts,id'],
        ])->validate();
        Log::info('validate package success', [$this->attributes['sender_name']]);

        $this->items = Validator::make($items, [
            '*.qty' => ['required', 'numeric'],
            '*.name' => 'required',
            '*.desc' => 'nullable',
            '*.weight' => ['required', 'numeric'],
            '*.height' => ['required', 'numeric'],
            '*.length' => ['required', 'numeric'],
            '*.width' => ['required', 'numeric'],
            '*.is_insured' => ['nullable', 'boolean'],
            '*.price' => ['required_if:*.is_insured,true', 'numeric'],
            '*.handling' => ['nullable', 'array'],
            '*.handling.*' => ['string', Rule::in(Handling::getTypes())],
            '*.is_glassware' => ['nullable', 'boolean'],
        ])->validate();
        Log::info('validate package items success', [$this->attributes['sender_name']]);

        $items = [];
        foreach ($this->items as $item) {
            $item['height'] = ceil($item['height']);
            $item['length'] = ceil($item['length']);
            $item['width'] = ceil($item['width']);
            $item['weight'] = $this->ceilByTolerance($item['weight']);

            array_push($items, $item);
        }
        $this->items = $items;
        $this->isSeparate = $isSeparate;
        $this->package = new Package();
        Log::info('prepared finished. ', [$this->attributes['sender_name']]);
    }

    /**
     * Handle the job.
     *
     * @return bool
     */
    public function handle(): bool
    {
        Log::info('Running job. ', [$this->attributes['sender_name']]);
        if (is_null($this->attributes['sender_address'])) {
            /** @var Regency $regency */
            $regency = Regency::query()->find($this->attributes['origin_regency_id']);
            $this->attributes['sender_address'] = $regency->name.', '.$regency->province->name;
        }

        $this->attributes['sender_address'] = substr($this->attributes['sender_address'], 0, 255);

        $this->package->fill($this->attributes);
        $this->package->is_separate_item = $this->isSeparate;
        $this->package->created_by = auth()->user()->id;
        $this->package->save();
        Log::info('trying insert package to db. ', [$this->attributes['sender_name']]);

        if ($this->package->exists) {
            foreach ($this->items as $attributes) {
                $item = new Item();
                $attributes['package_id'] = $this->package->id;

                $item->fill($attributes);
                $item->save();
            }
            Log::info('after saving package items success. ', [$this->attributes['sender_name']]);
            Log::info('triggering event. ', [$this->attributes['sender_name']]);
            event(new PackageCreated($this->package, $this->attributes['partner_code']));
        }
        return $this->package->exists;
    }


    public static function ceilByTolerance(float $weight = 0)
    {
        // decimal tolerance .3
        $whole = $weight;
        $maj = (int) $whole; //get major
        $min = $whole - $maj; //get after point

        // check with tolerance
        $min = (int) ($min >= self::MIN_TOL ? 1 : 0);

        $weight = $maj + $min;

        return $weight;
    }
}
