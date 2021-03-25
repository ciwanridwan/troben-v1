<?php

namespace Database\Factories\Partners;

use App\Models\Partners\Transporter;
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
            Transporter::TYPE_CDE_ENGKEL_BOX,
            Transporter::TYPE_CDE_ENGKEL_DOUBLE,
            Transporter::TYPE_CDE_ENGKEL_DOUBLE_BOX,
            Transporter::TYPE_FUSO_6M,
            Transporter::TYPE_FUSO_9M,
            Transporter::TYPE_TRONTON,
            Transporter::TYPE_WINGBOX,
            Transporter::TYPE_VAN,
        ];

        return [
            'production_year' => $productionYear = $this->faker->year,
            'registration_name' => $this->faker->name,
            'registration_year' => (int) $productionYear + 2,
            'registration_number' => strtoupper($this->faker->randomLetter.' '.$this->faker->randomNumber(4).' '.$this->faker->randomLetter.$this->faker->randomLetter),
            'type' => $typeMapper[$this->faker->randomKey(range(0, 9))],
            'is_verified' => true,
            'verified_at' => now(),
        ];
    }
}
