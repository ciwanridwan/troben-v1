<?php

namespace Tests\Casts\Code\Log;

use App\Models\Packages\Package;
use App\Supports\Translates\Translate;
use Database\Seeders\Packages\CashierInChargeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
