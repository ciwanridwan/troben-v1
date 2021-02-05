<?php

namespace Database\Factories\Partners;

use App\Models\Partners\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class WarehouseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Warehouse::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'partner_id' => null,
            'geo_province_id' => null,
            'geo_regency_id' => null,
            'geo_district_id' => null,
            'code' => $this->faker->citySuffix,
            'name' => $this->faker->userName,
            'address' => $this->faker->address,
            'geo_area' => null,
            'is_pool' => $this->faker->boolean(),
            'is_counter' => $this->faker->boolean(),
        ];
    }
}
