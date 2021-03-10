<?php

namespace Database\Seeders;

use App\Models\Handling;
use Faker\Factory;
use Illuminate\Database\Seeder;

class HandlingSeeder extends Seeder
{
    public array $handling = [
        [
            'name' => 'Kayu',
            'type' => Handling::TYPE_VOLUME,
        ],
        [
            'name' => 'Plastik',
            'type' => Handling::TYPE_VOLUME,
        ],
        [
            'name' => 'Bubble Wrap',
            'type' => Handling::TYPE_VOLUME,
        ],
        [
            'name' => 'Kardus',
            'type' => Handling::TYPE_VOLUME,
        ],
        [
            'name' => 'Pallate',
            'type' => Handling::TYPE_VOLUME,
        ],
        [
            'name' => 'Karung',
            'type' => Handling::TYPE_VOLUME,
        ],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Factory::create();

        foreach ($this->handling as $value) {
            Handling::query()->create(array_merge($value, [
                'price' => $faker->numberBetween(1000, 100000),
            ]));
        }
    }
}
