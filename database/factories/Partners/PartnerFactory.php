<?php

namespace Database\Factories\Partners;

use App\Models\Partners\Partner;
use Illuminate\Database\Eloquent\Factories\Factory;

class PartnerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Partner::class;

    private static int $chosenType = 0;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $typeMapper = [
            ['type' => Partner::TYPE_BUSINESS, 'code' => 'MB'],
            ['type' => Partner::TYPE_POOL, 'code' => 'MPW'],
            ['type' => Partner::TYPE_SPACE, 'code' => 'MS'],
            ['type' => Partner::TYPE_TRANSPORTER, 'code' => 'MTM'],
        ];

        $no = self::$chosenType++;

        if (self::$chosenType === 4) {
            self::$chosenType = 0;
        }

        return [
            'name' => $this->faker->company,
            'code' => strtoupper($typeMapper[$no]['code'].'-'.$this->faker->StateAbbr().'-'.str_pad($this->faker->unique()->randomNumber(3), 3, 0, STR_PAD_LEFT)),
            'contact_email' => $this->faker->email,
            'contact_phone' => $this->faker->phoneNumber,
            'address' => $this->faker->address,
            'geo_location' => null,
            'type' => $typeMapper[$no]['type'],
        ];
    }
}
