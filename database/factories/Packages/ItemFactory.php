<?php

namespace Database\Factories\Packages;

use App\Models\Handling;
use App\Models\Packages\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Item::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'qty' => $this->faker->numberBetween(1, 3),
            'name' => $this->faker->text(9),
            'weight' => $this->faker->numberBetween(1, 10),
            'width' => $this->faker->numberBetween(1, 40),
            'length' => $this->faker->numberBetween(1, 40),
            'height' => $this->faker->numberBetween(1, 40),
            'price' => $this->faker->randomElement([100000, 2000000, 4000000, 5000000, 19000, 900000]),
            'handling' => Handling::query()->take($this->faker->numberBetween(1, Handling::query()->count()))->get(),
        ];
    }
}
