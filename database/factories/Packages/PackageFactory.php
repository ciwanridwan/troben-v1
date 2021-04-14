<?php

namespace Database\Factories\Packages;

use App\Models\Price;
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

        $price = Price::query()->inRandomOrder()->first();

        /** @var SubDistrict $originSubDistrict */
        // $originSubDistrict = SubDistrict::query()->where('id', '!=', $originSubDistrict->id)->first();
        /** @var SubDistrict $destinationSubDistrict */
        $destinationSubDistrict = $price->destination;


        return [
            'service_code' => $service->code,
            'sender_name' => $this->faker->name,
            'sender_phone' => $this->faker->phoneNumber,
            'sender_address' => $this->faker->address,
            'receiver_name' => $this->faker->name,
            'receiver_phone' => $this->faker->phoneNumber,
            'receiver_address' => $this->faker->address,
            'origin_regency_id' => $price->origin_regency_id,
            'origin_district_id' => $price->origin_district_id,
            'origin_sub_district_id' => $price->origin_sub_district_id,
            'destination_regency_id' => $destinationSubDistrict->regency_id,
            'destination_district_id' => $destinationSubDistrict->district_id,
            'destination_sub_district_id' => $destinationSubDistrict->id,
        ];
    }
}
