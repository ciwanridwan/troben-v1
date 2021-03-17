<?php

namespace Tests\Jobs\Packages\Partners;

use App\Jobs\Packages\Partners\AssignFirstPartnerToPackage;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use App\Models\User;
use Database\Seeders\PackagesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssignFirstPartnerToPackageTest extends TestCase
{
    use RefreshDatabase;

    public bool $seed = true;

    public function test_on_valid_data()
    {
        $this->seed(PackagesSeeder::class);

        /** @var User $user */
        $user = User::query()->first();

        /** @var Package $package */
        $package = Package::query()->first();
        /** @var Partner $partner */
        $partner = $user->partners()->first();

        $job = new AssignFirstPartnerToPackage($package, $partner);

        dispatch_now($job);

        $this->assertDatabaseHas('delivery_package', [
            'package_id' => $package->id,
        ]);

        // get first partner
        $this->assertEquals($partner->fresh(), $job->package->deliveries()->first()->partner);
    }
}
