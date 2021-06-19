<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

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
            'email' => $this->faker->unique()->safeEmail,
            'phone' => PhoneNumberUtil::getInstance()->format(
                PhoneNumberUtil::getInstance()->parse('08' . str_pad(random_int(0, 9999), 8, '0', STR_PAD_LEFT), 'ID'),
                PhoneNumberFormat::E164
            ),
            'email_verified_at' => now(),
            'password' => 'password',
            'remember_token' => Str::random(10),
        ];
    }
}
