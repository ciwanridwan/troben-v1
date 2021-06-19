<?php

namespace App\Actions\CustomerService\WalkIn;

use App\Events\Packages\PackageApprovedByCustomer;
use App\Jobs\Packages\Actions\AssignFirstPartnerToPackage;
use App\Jobs\Packages\CreateNewPackage;
use App\Models\Customers\Customer;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Validator;
use Veelasky\LaravelHashId\Rules\ExistsByHash;

class CreateWalkinOrder
{
    use DispatchesJobs;
    /**
     * @var array
     */
    protected array $inputs;

    /**
     * @var array
     */
    protected array $items;

    /**
     * @var Partner
     */
    protected Partner $partner;

    /**
     * @param Partner $partner
     * @param array $inputs
     */
    function __construct(Partner $partner, array $inputs)
    {
        foreach ($inputs as $key => $value) {
            if (is_string($value)) {
                $inputs[$key] = json_decode($value);
            }
        }

        Validator::validate($inputs, [
            'items' => ['required'],
            'customer_hash' => ['required', new ExistsByHash(Customer::class)]
        ]);

        $this->partner = $partner;

        $this->inputs = $inputs;
    }

    public function create()
    {
        $this->preparedPackageStore();
        $job = new CreateNewPackage($this->inputs, $this->items);

        $this->dispatch($job);

        /** @var Package $package */
        $package = $job->package;

        $job = new AssignFirstPartnerToPackage($package, $this->partner);

        $package->refresh();

        $package->setAttribute('status', Package::STATUS_WAITING_FOR_APPROVAL);

        event(new PackageApprovedByCustomer($package));

        return $job;
    }

    /**
     * @param Partner $partner
     * @param mixed $inputs
     *
     * @return array
     */
    public function preparedPackageStore(): array
    {


        $this->inputs['customer_id'] = Customer::byHash($this->inputs['customer_hash'])->id;

        $this->inputs['sender_address'] = $this->partner->geo_address;
        $this->inputs['origin_regency_id'] = $this->partner->geo_regency_id;
        $this->inputs['origin_district_id'] = $this->partner->geo_district_id;
        $this->inputs['origin_sub_district_id'] = $this->partner->geo_sub_district_id;

        $items = $this->inputs['items'] ?? [];

        foreach ($items as $key => $item) {
            $items[$key] = (new Collection($item))->toArray();
        }
        $this->items = $items;

        return [
            'package' => $this->inputs,
            'items' => $this->items
        ];
    }
}
