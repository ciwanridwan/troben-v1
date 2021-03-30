<?php

namespace Database\Seeders\Packages;

use App\Models\User;
use Illuminate\Database\Seeder;
use App\Models\Deliveries\Delivery;
use Illuminate\Support\Facades\Event;
use Illuminate\Database\Eloquent\Model;
use App\Events\Deliveries\Pickup\DriverUnloadedPackageInWarehouse;

class FinishedDeliveriesSeeder extends Seeder
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
            /** @var User $driver */
            $driver = User::query()->whereHas('deliveries')->first();

            Event::listen(DriverUnloadedPackageInWarehouse::class,
                fn (DriverUnloadedPackageInWarehouse $event) => $this->command
                    ->warn('=> package from '.$event->delivery->packages->implode('sender_name').' status changed to estimating..'));

            $driver->deliveries()->take(ceil($driver->deliveries()->count() / 2))->get()
                ->tap(fn () => $this->command->getOutput()->info('Begin set package to estimating'))
                ->map(fn (Delivery $delivery) => new DriverUnloadedPackageInWarehouse($delivery))
                ->each(fn (DriverUnloadedPackageInWarehouse $event) => event($event));
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
        $this->call(AssignedPackagesSeeder::class);
    }
}
