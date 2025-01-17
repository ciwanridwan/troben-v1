<?php

namespace Tests\Jobs\Packages\Partners;

use Tests\TestCase;
use App\Models\User;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use Database\Seeders\Packages\PackagesTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Jobs\Packages\Actions\AssignFirstPartnerToPackage;

class AssignFirstPartnerToPackageTest extends TestCase
{
    use RefreshDatabase;

    public bool $seed = true;

    public function test_on_valid_data()
    {
        $this->seed(PackagesTableSeeder::class);

        /** @var User $user */
        $user = User::query()->first();

        /** @var Package $package */
        $package = Package::query()->first();
        /** @var Partner $partner */
        $partner = $user->partners()->first();

        $job = new AssignFirstPartnerToPackage($package, $partner);

        dispatch_now($job);

        $this->assertDatabaseHas('deliverables', [
            'deliverable_id' => $package->id,
        ]);

        // get first partner
        $this->assertEquals($partner->fresh(), $job->package->deliveries()->first()->partner);
        $this->assertSame(Package::STATUS_PENDING, $package->fresh()->status);
    }
}
