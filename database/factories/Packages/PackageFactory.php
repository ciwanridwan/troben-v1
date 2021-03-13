<?php

namespace Database\Factories\Packages;

use App\Models\Service;
use App\Models\Geo\SubDistrict;
use App\Models\Packages\Package;
use Illuminate\Database\Eloquent\Factories\Factory;

class PackageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Package::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        /** @var Service $service */
        $service = Service::query()->inRandomOrder()->firstOrFail();

        /** @var SubDistrict $originSubDistrict */
        $originSubDistrict = SubDistrict::query()->inRandomOrder()->first();
        /** @var SubDistrict $destinationSubDistrict */
        $destinationSubDistrict = SubDistrict::query()->where('id', '!=', $originSubDistrict->id)->first();

        return [
            'service_code' => $service->code,
            'receiver_name' => $this->faker->name,
            'receiver_phone' => $this->faker->phoneNumber,
            'receiver_address' => $this->faker->address,
            'origin_regency_id' => $originSubDistrict->regency_id,
            'origin_district_id' => $originSubDistrict->district_id,
            'origin_sub_district_id' => $originSubDistrict->id,
            'destination_regency_id' => $destinationSubDistrict->regency_id,
            'destination_district_id' => $destinationSubDistrict->district_id,
            'destination_sub_district_id' => $destinationSubDistrict->id,
        ];
    }
}
