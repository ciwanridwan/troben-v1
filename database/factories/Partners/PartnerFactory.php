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
        $no = $this->faker->randomKey([0,1,2,3]);

        return [
            'name' => $this->faker->name,
            'code' => $typeMapper[$no]['code'].'-'.$this->faker->StateAbbr().'-'.$this->faker->unique()->randomDigit(),
            'contact_email' => null,
            'contact_phone' => null,
            'address' => null,
            'geo_location' => null,
            'type' => $typeMapper[$no]['type'],
        ];
    }
}
