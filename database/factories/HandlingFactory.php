<?php

namespace Database\Factories;

use App\Models\Handling;
use Illuminate\Database\Eloquent\Factories\Factory;

class HandlingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Handling::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $typeMapper = [Handling::TYPE_VOLUME,Handling::TYPE_WEIGHT];
        return [
            'name' => $this->faker->name,
            'price' => $this->faker->randomFloat(2,3000,10000),
            'type' => $typeMapper[$this->faker->randomKey([0,1])],
        ];
    }
}
