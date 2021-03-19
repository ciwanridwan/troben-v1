<?php

namespace Tests\Jobs\Packages\Partners;

use Tests\TestCase;
use App\Models\User;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Jobs\Packages\Partners\AssignFirstPartnerToPackage;

class AssignFirstPartnerToPackageTest extends TestCase
{
    use RefreshDatabase;

    public bool $seed = true;

    public function test_on_valid_data()
    {
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
