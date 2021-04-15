<?php

namespace Database\Factories\Deliveries;

use App\Models\Deliveries\Delivery;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeliveryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Delivery::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'type' => Delivery::TYPE_TRANSIT,
            'status' => Delivery::STATUS_WAITING_ASSIGN_PACKAGE,
            'origin_regency_id' => null,
            'origin_district_id' => null,
            'origin_sub_district_id' => null,
            'destination_regency_id' => null,
            'destination_district_id' => null,
            'destination_sub_district_id' => null,
            'partner_id' => null,
        ];
    }
}
