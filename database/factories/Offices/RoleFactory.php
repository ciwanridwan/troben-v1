<?php

namespace Database\Factories\Offices;

use App\Models\Offices\Office;
use App\Models\Offices\Role;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

class RoleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Role::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
