<?php

namespace Tests\Http\Api\Order;

use Tests\TestCase;
use App\Models\Handling;
use Database\Seeders\HandlingSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CustomerOrderApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public bool $seed = true;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_can_make_an_order()
    {
        $this->seed(HandlingSeeder::class);

        $headers = $this->getCustomersHeader();

        $services = collect($this->getJson(route('api.service'), $headers)->json('data'));
        $subDistricts = collect($this->getJson(route('api.geo', ['type' => 'sub_district']))->json('data'));

        $originSubDistrict = $subDistricts->random();
        $destinationSubDistrict = $subDistricts->where('id', '!=', $originSubDistrict['id'])->random();

        $response = $this->postJson(route('api.order.store'), [
            'service_code' => $services->random()['code'],
            'receiver_name' => $this->faker->name,
            'receiver_phone' => $this->faker->phoneNumber,
            'receiver_address' => $this->faker->address,
            'origin_regency_id' => $originSubDistrict['regency']['id'],
            'origin_district_id' => $originSubDistrict['district']['id'],
            'origin_sub_district_id' => $originSubDistrict['id'],
            'destination_regency_id' => $destinationSubDistrict['regency']['id'],
            'destination_district_id' => $destinationSubDistrict['district']['id'],
            'destination_sub_district_id' => $destinationSubDistrict['id'],
            'items' => collect(range(0, 3))->map(fn () => [
                'qty' => $this->faker->numberBetween(1, 3),
                'name' => $this->faker->text(9),
                'weight' => $this->faker->numberBetween(1, 10),
                'width' => $this->faker->numberBetween(1, 40),
                'length' => $this->faker->numberBetween(1, 40),
                'height' => $this->faker->numberBetween(1, 40),
                'handling' => Handling::query()->take($this->faker->numberBetween(1, Handling::query()->count()))->get(),
            ])->toArray(),
        ], $headers);

        $response->assertSuccessful();
    }
}
