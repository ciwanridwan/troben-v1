<?php

namespace Database\Seeders;

use App\Models\Products\Product;
use Illuminate\Database\Seeder;

class ProductsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        collect([
            [
                'name' => 'TrawlPack',
                'description' => null,
                'is_enabled' => true,
            ],
            [
                'name' => 'TrawlMover',
                'description' => null,
                'is_enabled' => false,
            ],
            [
                'name' => 'TrawlCarrier',
                'description' => null,
                'is_enabled' => false,
            ],
            [
                'name' => 'TrawlHeavy',
                'description' => null,
                'is_enabled' => false,
            ],
            [
                'name' => 'TrawlFeet',
                'description' => null,
                'is_enabled' => false,
            ],
            [
                'name' => 'TrawlTruck',
                'description' => null,
                'is_enabled' => false,
            ],
            [
                'name' => 'TrawlBoat',
                'description' => null,
                'is_enabled' => false,
            ],
            [
                'name' => 'TrawlExim',
                'description' => null,
                'is_enabled' => false,
            ],
        ])->each(fn ($v) => Product::factory(1)->create($v));
    }
}
