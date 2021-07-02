<?php

namespace Tests\Casts\Code\Log;

use App\Casts\Code\Log\Description as LogDescription;
use App\Events\Packages\PackageCreated;
use App\Models\Packages\Package;
use App\Supports\Translates\Package as TranslatesPackage;
use App\Supports\Translates\Translate;
use Database\Seeders\Packages\AssignedPackagesSeeder;
use Database\Seeders\Packages\CashierInChargeSeeder;
use Database\Seeders\Packages\PackagesTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DescriptionTest extends TestCase
{
    use RefreshDatabase;

    public bool $seed = true;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_created_package()
    {
        $this->seed(CashierInChargeSeeder::class);
        /** @var Package $package */
        $package = Package::query()->where('status', Package::STATUS_PENDING)->first();
        $descFormat = (new Translate($package))->translate();

        self::assertNotNull($descFormat);
    }
}
