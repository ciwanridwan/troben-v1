<?php

namespace Database\Seeders\Packages;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use App\Models\Deliveries\Delivery;
use Illuminate\Support\Facades\Event;
use Illuminate\Database\Eloquent\Model;
use App\Events\Deliveries\Pickup\DriverUnloadedPackageInWarehouse;

class WarehouseInChargeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->prepareDependency();

        self::setModelGuardedAndQueueToSync(function () {
            Event::listen(DriverUnloadedPackageInWarehouse::class,
                fn (DriverUnloadedPackageInWarehouse $event) => $this->command
                    ->warn('=> package from '.$event->delivery->packages->implode('sender_name').' status changed to "waiting for estimating"..'));

            User::query()->whereHas('deliveries')->each(fn(User $driver) => $driver->deliveries()
                ->where('status', Delivery::STATUS_ACCEPTED)->take(ceil($driver->deliveries()->count() / 2))
                ->get()
                ->tap(fn (Collection $collection) => $this->command->getOutput()->info('[FOR WAREHOUSE] Begin set package to "waiting for estimating" ['.$collection->count().']'))
                ->map(fn (Delivery $delivery) => new DriverUnloadedPackageInWarehouse($delivery))
                ->each(fn (DriverUnloadedPackageInWarehouse $event) => event($event)));
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
        if (Delivery::query()->where('status', Delivery::STATUS_ACCEPTED)->count() == 0) {
            $this->call(AssignedPackagesSeeder::class);
        }
    }
}
