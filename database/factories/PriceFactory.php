<?php

namespace Database\Factories;

use App\Models\Price;
use Illuminate\Database\Eloquent\Factories\Factory;

class PriceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Price::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'origin_province_id' => null,
            'origin_regency_id' => null,
            'origin_district_id' => null,
            'origin_sub_district_id' => null,
            'destination_id' => null,
            'zip_code' => null,
            'service_code' => null,
            'tier_1' => $this->faker->randomFloat(2,2000),
            'tier_2' => $this->faker->randomFloat(2,2000),
            'tier_3' => $this->faker->randomFloat(2,2000),
            'tier_4' => $this->faker->randomFloat(2,2000),
            'tier_5' => $this->faker->randomFloat(2,2000),
            'tier_6' => $this->faker->randomFloat(2,2000),
            'tier_7' => $this->faker->randomFloat(2,2000),
            'tier_8' => $this->faker->randomFloat(2,2000),
            'tier_9' => $this->faker->randomFloat(2,2000),
            'tier_10' => $this->faker->randomFloat(2,2000),
        ];
    }
}
