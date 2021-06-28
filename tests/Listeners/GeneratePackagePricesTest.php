<?php

namespace Tests\Listeners;

use App\Actions\Pricing\PricingCalculator;
use Tests\TestCase;
use App\Models\Packages\Package;
use App\Events\Packages\PackageCreated;
use App\Models\Geo\Regency;
use App\Models\Packages\Item;
use App\Models\Packages\Price;
use Database\Seeders\Packages\PackagesTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;

class GeneratePackagePricesTest extends TestCase
{
    use RefreshDatabase;

    public bool $seed = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PackagesTableSeeder::class);
    }

    public function test_on_item_created()
    {
        /** @var Package $package */
        $package = Package::first();
        $event = new PackageCreated($package);
        event($event);
        // dd($package->total_amount, $package->prices()->get()->sum('amount'));
        /** @var Regency $origin_regency */
        $origin_regency = $package->origin_regency()->first();
        $items = $package->items()->get();

        $items = $items->map(function (Item $item) {
            $item = $item->toArray();
            $item['handling'] = array_column($item['handling'], 'type');
            return $item;
        })->toArray();
        $inputs = [
            'origin_province_id' => $origin_regency->province_id,
            'origin_regency_id' => $origin_regency->id,
            'destination_id' => $package->destination_sub_district_id,
            'items' => $items
        ];
        $response = PricingCalculator::calculate($inputs, 'array');
        $result = $response['result'];
        self::assertSame($result['total_weight_borne'], $package->total_weight);
    }
}
