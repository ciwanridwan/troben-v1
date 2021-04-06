<?php

namespace Database\Seeders\Packages;

use App\Events\Packages\PackageCheckedByCashier;
use App\Models\Packages\Package;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Event;

class CustomerInChargeSeeder extends Seeder
{
    public function run()
    {
        $this->prepareDependency();

        self::setModelGuardedAndQueueToSync(function () {
            Event::listen(PackageCheckedByCashier::class, fn(PackageCheckedByCashier $event) => $this->command
                ->warn('=> package from '.$event->package->sender_name.' status changed to "waiting for approval"..'));

            $query = Package::query()->where('status', Package::STATUS_ESTIMATED);

            $query->take(ceil($query->count() / 2))
                ->get()
                ->tap(fn (Collection $collection) => $this->command->getOutput()->info('[FOR CUSTOMER] Begin set package to "waiting for approval" ['.$collection->count().']'))
                ->map(fn (Package $package) => new PackageCheckedByCashier($package))
                ->each(fn (PackageCheckedByCashier $event) => event($event));
        });
    }

    private static function setModelGuardedAndQueueToSync(\Closure $callback)
    {
        Model::reguard();
        $originalValue = config('queue.default');
        config()->set('queue.default', 'sync');
        $callback();
        config()->set('queue.default', $originalValue);
        Model::unguard();
    }

    private function prepareDependency()
    {
        if (Package::query()->where('status', Package::STATUS_ESTIMATED)->count() == 0) {
            $this->call(CashierInChargeSeeder::class);
        }
    }
}
