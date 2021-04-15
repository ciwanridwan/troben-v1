<?php

namespace Tests\Feature;

use App\Models\Packages\Package;
use App\Models\User;
use Database\Seeders\Packages\PackagesTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GenerateCodeTest extends TestCase
{
    use RefreshDatabase;

    public bool $seed = true;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_on_package()
    {
        $this->seed(PackagesTableSeeder::class);

        $package = Package::query()->first();
        dd($package->barcode);
    }
}
