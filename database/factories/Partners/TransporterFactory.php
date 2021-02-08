<?php

namespace Database\Factories\Partners;

use App\Models\Partners\Transporter;
use Faker\Provider\ms_MY\Miscellaneous;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransporterFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Transporter::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // $this->faker = $this->faker->addProvider(new Miscellaneous($this->faker));
        $typeMapper = [
            Transporter::TYPE_BIKE,
            Transporter::TYPE_MPV,
            Transporter::TYPE_PICKUP,
            Transporter::TYPE_PICKUP_BOX,
            Transporter::TYPE_CDE_ENGKEL,
            Transporter::TYPE_ENGKEL_BOX,
            Transporter::TYPE_ENGKEL_DOUBLE,
            Transporter::TYPE_ENGKEL_DOUBLE_BOX,
            Transporter::TYPE_FUSO_6M,
            Transporter::TYPE_FUSO_9M,
            Transporter::TYPE_TRONTON,
            Transporter::TYPE_WINGBOX,
        ];
        return [
            'partner_id' => null,
            'name' => $this->faker->userName,
            // 'registration_number' => $this->faker->jpjNumberPlate(),
            'registration_number' => $this->faker->lastName,
            'type' => $typeMapper[$this->faker->randomKey([0,1,2,3,4,5,6,7,8,9,10,11])],
        ];
    }
}
