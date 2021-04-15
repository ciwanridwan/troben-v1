<?php

namespace Tests\Feature;

use App\Models\Packages\Item;
use App\Models\Packages\Package;
use App\Models\User;
use Database\Seeders\Packages\PackagesTableSeeder;
use Database\Seeders\StagingDatabaseSeeder;
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

        $item = Item::query()->where('qty', '>', 1)->inRandomOrder()->first();
        $item->qty = 10;
        $item->save();
    }
}
