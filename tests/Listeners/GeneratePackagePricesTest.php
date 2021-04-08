<?php

namespace Tests\Listeners;

use Tests\TestCase;
use App\Models\Packages\Package;
use App\Events\Packages\PackageCreated;
use Database\Seeders\Packages\PackagesTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
