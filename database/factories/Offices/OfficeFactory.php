<?php

namespace Database\Factories\Offices;

use App\Models\Offices\Office;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

class OfficeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Office::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'username' => $this->faker->unique()->userName,
            'phone' => PhoneNumberUtil::getInstance()->format(
                PhoneNumberUtil::getInstance()->parse('08'.str_pad(random_int(0, 9999), 8, '0', STR_PAD_LEFT), 'ID'),
                PhoneNumberFormat::E164
            ),
            'address' => $this->faker->unique()->address,
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'password',
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
