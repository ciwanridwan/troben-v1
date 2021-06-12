<?php

namespace Tests\Jobs\Deliveries\Actions;

use App\Events\Packages\PackageCanceledByCustomer;
use App\Events\Packages\PackageCancelMethodSelected;
use App\Events\Packages\PackageCheckedByCashier;
use App\Jobs\Packages\Actions\SelectCanceledPickupMethodDelivered;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Package;
use Database\Seeders\Packages\CashierInChargeSeeder;
use Database\Seeders\PaymentGatewaySeeder;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateNewManifestForReturnPackageTest extends TestCase
{
    use RefreshDatabase, DispatchesJobs;

    public bool $seed = true;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CashierInChargeSeeder::class);
        $this->seed(PaymentGatewaySeeder::class);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_on_package_canceled_delivered_by_driver()
    {
        /** @var Package $package */
        $package = Package::all()->random()->first();
        /** @var Delivery $delivery */
        event(new PackageCheckedByCashier($package));

        $package->refresh();

        event(new PackageCanceledByCustomer($package));

        $package->refresh();

        $this->expectsEvents(PackageCancelMethodSelected::class);
        $job = new SelectCanceledPickupMethodDelivered($package);
        $this->dispatch($job);
        $package = $job->package;
    }
}
