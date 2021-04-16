<?php

namespace Database\Seeders\Packages\PostPayment;

use Illuminate\Database\Seeder;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use App\Models\Deliveries\Delivery;

class ManifestSeeder extends Seeder
{
    public function run(): void
    {
        if (Partner::query()->where('type', Partner::TYPE_BUSINESS)->count() === 1) {
            $this->command->warn('manifest seeder only work while UsersTableSeeder::$COUNT > 1');

            return;
        }

        $this->prepareDependency();

        /** @var Package $package */
        $package = Package::query()->where('status', Package::STATUS_PACKED)->first();
        /** @var Delivery $delivery */
        $delivery = $package->deliveries->first();
        $partner = $delivery->partner;
        $otherPartners = Partner::query()->where('id', '!=', $partner->id)->whereIn('type', [
            Partner::TYPE_BUSINESS,
        ])->get();

        $factory = Delivery::factory()->count($otherPartners->count());

        $factory->create()
            ->each(fn (Delivery $delivery, $index) => $delivery->fill([
                'type' => Delivery::TYPE_TRANSIT,
                'status' => Delivery::STATUS_WAITING_ASSIGN_PACKAGE,
                'origin_regency_id' => $partner->geo_regency_id,
                'origin_district_id' => $partner->geo_district_id,
                'origin_sub_district_id' => $partner->geo_sub_district_id,
                'destination_regency_id' => $otherPartners->get($index)->geo_regency_id,
                'destination_district_id' => $otherPartners->get($index)->geo_district_id,
                'destination_sub_district_id' => $otherPartners->get($index)->geo_sub_district_id,
                'origin_partner_id' => $partner->id,
                'partner_id' => $otherPartners->get($index)->id,
            ]))
            ->each(fn (Delivery $delivery) => $delivery->save());
    }

    private function prepareDependency(): void
    {
        if (Package::query()->where('status', Package::STATUS_PACKED)->count() === 0) {
            $this->call(PackedSeeder::class);
        }
    }
}
