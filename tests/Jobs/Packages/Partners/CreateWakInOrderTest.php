<?php

namespace Tests\Jobs\Packages\Partners;

use App\Actions\CustomerService\WalkIn\CreateWalkinOrder;
use Tests\TestCase;
use App\Models\User;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use Database\Seeders\Packages\PackagesTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Jobs\Packages\Actions\AssignFirstPartnerToPackage;
use App\Jobs\Packages\CreateNewPackage;
use App\Models\Customers\Customer;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Item;
use App\Models\Price;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Validation\ValidationException;

class CreateWakInOrderTest extends TestCase
{
    use RefreshDatabase, DispatchesJobs;

    public bool $seed = true;

    public function test_on_valid_data()
    {
        /** @var User $user */
        $user = User::query()->first();

        /** @var Customer $customer */
        $customer = Customer::first();

        /** @var Partner $partner */
        $partner = $user->partners()->first();
        $price = Price::inRandomOrder()->first();
        $partner->setAttribute('geo_province_id', $price->regency->province_id);
        $partner->setAttribute('geo_regency_id', $price->regency->id);
        $partner->save();

        $package = Package::factory()->makeOne()->getAttributes();

        $package['items'] = [];
        $package['customer_hash'] = $customer->hash;

        Item::factory()->count(3)->make()->each(function (Item $item) use (&$package) {
            $item = $item->getAttributes();
            $item['handling'] = json_decode($item['handling']);
            $item['handling'] = array_column($item['handling'], 'type');
            $package['items'][] = $item;
        });

        foreach ($package as $key => $value) {
            if (is_string($value)) {
                $package[$key] = json_encode($value);
            }
        }


        $package = (new CreateWalkinOrder($partner, $package))->create();
        $this->assertDatabaseHas('packages', array_merge($package->only('id'), [
            'status' => Package::STATUS_WAITING_FOR_APPROVAL
        ]));

        $delivery = $package->deliveries->first();
        $this->assertDatabaseHas('deliveries', [
            'id' => $delivery->id,
            'type' => Delivery::TYPE_PICKUP,
            'status' => Delivery::STATUS_FINISHED
        ]);
    }
}
