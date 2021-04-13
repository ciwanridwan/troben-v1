<?php

namespace Database\Seeders\Packages\PostPayment;

use App\Models\Packages\Package;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class PackedSeeder extends Seeder
{
    public function run()
    {
        $this->prepareDependency();

        $query = Package::query()->where('status', Package::STATUS_WAITING_FOR_PACKING);

        $query->take(floor($query->count() / 2))->get()
            ->tap(fn(Collection $collection) => $this->command->getOutput()->title('Set package to packed ['.$collection->count().'/'.$query->count().']'))
            ->each(fn(Package $package) => $package->update([
                'status' => Package::STATUS_PACKED,
                'packager_id' => $package->estimator_id,
            ]))
            ->each(fn(Package $package) => $this->command->warn(' => package from '.$package->sender_name.' set to packed...'));
    }

    private function prepareDependency()
    {
        if (Package::query()->where('status', Package::STATUS_WAITING_FOR_PACKING)->count() == 0) {
            $this->call(PostPaymentSeeder::class);
        }
    }
}
