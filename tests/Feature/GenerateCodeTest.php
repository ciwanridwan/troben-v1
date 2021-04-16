<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Packages\Item;
use Database\Seeders\Packages\PackagesTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
