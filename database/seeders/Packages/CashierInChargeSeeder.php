<?php

namespace Database\Seeders\Packages;

use App\Events\Packages\PackageEstimatedByWarehouse;
use App\Events\Packages\WarehouseIsEstimatingPackage;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use App\Models\Partners\Pivot\UserablePivot;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Event;

class CashierInChargeSeeder extends Seeder
{
    public function run()
    {
        $this->prepareDependency();

        self::setModelGuardedAndQueueToSync(function () {
            /** @var Partner $partner */
            $partner = Partner::query()->whereHas('deliveries', fn(Builder $builder) => $builder->where('status', Delivery::STATUS_FINISHED))->first();

            /** @var \App\Models\User $warehouseUser */
            $warehouseUser = $partner->users()->wherePivot('role', UserablePivot::ROLE_WAREHOUSE)->first();

            auth()->setUser($warehouseUser);

            $query = Package::query()
                ->whereHas('deliveries', fn(Builder $builder) => $builder->where('partner_id', $partner->id)->where('type', Delivery::TYPE_PICKUP))
                ->where('status', Package::STATUS_WAITING_FOR_ESTIMATING);

            Event::listen(PackageEstimatedByWarehouse::class,
                fn(PackageEstimatedByWarehouse $event) => $this->command->warn('package from '.$event->package->sender_name.' status set to "estimated"'));

            $query->take(ceil($query->count() / 2))
                ->get()
                ->tap(fn(Collection $collection) => $this->command->getOutput()->info('[FOR CASHIER] Begin set package to "estimated" ['.$collection->count().']'))
                ->map(fn(Package $package) => new WarehouseIsEstimatingPackage($package))
                ->each(fn(WarehouseIsEstimatingPackage $event) => event($event))
                ->map(fn(WarehouseIsEstimatingPackage $event) => new PackageEstimatedByWarehouse($event->package))
                ->each(fn(PackageEstimatedByWarehouse $event) => event($event));
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
        if (Package::query()->where('status', Package::STATUS_WAITING_FOR_ESTIMATING)->count() == 0) {
            $this->call(WarehouseInChargeSeeder::class);
        }
    }
}
