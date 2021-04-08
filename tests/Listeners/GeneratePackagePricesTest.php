<?php

namespace Tests\Listeners;

use Tests\TestCase;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use App\Models\Deliveries\Delivery;
use App\Models\Partners\Pivot\UserablePivot;
use App\Events\Packages\PackageCheckedByCashier;
use App\Events\Packages\PackageApprovedByCustomer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Events\Packages\PackageEstimatedByWarehouse;
use Database\Seeders\Packages\CashierInChargeSeeder;
use App\Events\Packages\WarehouseIsEstimatingPackage;
use Database\Seeders\Packages\AssignedPackagesSeeder;
use Database\Seeders\Packages\CustomerInChargeSeeder;
use App\Listeners\Packages\UpdatePackageStatusByEvent;
use Database\Seeders\Packages\WarehouseInChargeSeeder;
use App\Events\Deliveries\Pickup\PackageLoadedByDriver;
use App\Events\Deliveries\Pickup\DriverUnloadedPackageInWarehouse;
use App\Events\Packages\PackageCreated;
use App\Events\Packages\PackageUpdated;
use Database\Seeders\Packages\PackagesTableSeeder;

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
        $package = Package::first();
        $event = new PackageCreated($package);
        event($event);
    }
}
