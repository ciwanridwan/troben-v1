<?php

namespace App\Jobs\Packages;

use App\Actions\Pricing\PricingCalculator;
use App\Models\Packages\Item;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Models\Packages\Package;
use App\Casts\Package\Items\Handling;
use App\Events\Packages\PackageCreated;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateNewPackageByCs
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
    protected array $packageAttributes;

    /**
     * Package items array.
     *
     * @var array
     */
    protected array $itemsAttributes;

    /**
     * Package items array.
     *
     * @var array
     */
    protected array $items;

    /**
     * Partner Code.
     *
     * @var string
     */
    protected string $code;

    /**
     * CreateNewPackage constructor.
     *
     * @param array $inputs
     * @param array $items
     * @param bool  $isSeparate
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __construct(array $packageAttr, array $itemsAttr, string $partnerCode)
    {
        $this->packageAttributes = $packageAttr;
        $this->code = $partnerCode;
        $this->itemsAttributes = Validator::make($itemsAttr, [
            '*.hash' => ['nullable'],
            '*.category_item_id' => ['numeric'], //disable validation for compability old package
            '*.is_glassware' => ['boolean'],
            '*.qty' => ['numeric'],
            '*.name' => ['string'],
            '*.price' => ['required_if:is_insured,true', 'numeric'],
            '*.desc' => ['string'],
            '*.weight' => ['numeric'],
            '*.height' => ['numeric'],
            '*.length' => ['numeric'],
            '*.width' => ['numeric'],
            '*.is_insured' => ['boolean'],
            '*.handling' => ['nullable', 'array'],
            '*.handling.*' => ['string', Rule::in(Handling::getTypes())],
        ])->validate();

        $items = [];
        foreach ($this->itemsAttributes as $item) {
            $item['height'] = ceil($item['height']);
            $item['length'] = ceil($item['length']);
            $item['width'] = ceil($item['width']);
            $item['weight'] = PricingCalculator::ceilByTolerance($item['weight']);

            array_push($items, $item);
        }
        $this->items = $items;
        $this->package = new Package();
    }

    /**
     * Handle the job.
     *
     * @return bool
     */
    public function handle(): bool
    {
        Log::info('Running job. ', [$this->packageAttributes['sender_name']]);

        $this->packageAttributes['sender_address'] = substr($this->packageAttributes['sender_address'], 0, 255);

        $this->package->fill($this->packageAttributes);
        $this->package->created_by = auth()->user()->id;
        $this->package->save();

        Log::info('trying insert package to db. ', [$this->packageAttributes['sender_name']]);

        if ($this->package->exists) {
            foreach ($this->items as $attributes) {
                $item = new Item();
                $attributes['package_id'] = $this->package->id;

                $item->fill($attributes);
                $item->save();
            }
            Log::info('after saving package items success. ', [$this->packageAttributes['sender_name']]);
            Log::info('triggering event. ', [$this->packageAttributes['sender_name']]);
            event(new PackageCreated($this->package, $this->code));
        }
        return $this->package->exists;
    }
}
