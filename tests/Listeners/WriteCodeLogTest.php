<?php

namespace Tests\Listeners;

use App\Events\Packages\PackageCreated;
use App\Listeners\Codes\WriteCodeLog;
use App\Models\Packages\Package;
use Database\Seeders\Packages\PackagesTableSeeder;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WriteCodeLogTest extends TestCase
{
    use RefreshDatabase;

    public bool $seed = true;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PackagesTableSeeder::class);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_write_code_log_on_package_created()
    {
        $package = Package::get()->random()->first();
        $this->actingAs($package->customer);
        event(new PackageCreated($package));
        Event::listen(PackageCreated::class, WriteCodeLog::class);
    }
}
