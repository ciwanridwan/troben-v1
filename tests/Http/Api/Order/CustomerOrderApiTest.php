<?php

namespace Tests\Http\Api\Order;

use App\Models\Customers\Customer;
use Tests\TestCase;
use App\Models\Handling;
use App\Models\Packages\Package;
use Database\Seeders\HandlingSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Database\Seeders\Packages\PackagesTableSeeder;
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

        $data = [
            'service_code' => $services->random()['code'],
            'sender_name' => $this->faker->name,
            'sender_phone' => $this->faker->phoneNumber,
            'sender_address' => $this->faker->address,
            'receiver_name' => $this->faker->name,
            'receiver_phone' => $this->faker->phoneNumber,
            'receiver_address' => $this->faker->address,
            'origin_regency_id' => $originSubDistrict['regency']['id'],
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
                'is_insured' => true,
                'handling' => Handling::query()->take($this->faker->numberBetween(1, Handling::query()->count()))->get()->map->id->toArray(),
            ])->toArray(),
        ];

        $response = $this->postJson(route('api.order.store'), $data, $headers);

        $response->assertSuccessful();
    }

    public function test_can_update_existing_order()
    {
        $this->seed(PackagesTableSeeder::class);

        /** @var Customer $customer */
        $customer = Customer::query()->first();

        $this->actingAs($customer);

        /** @var Package $package */
        $package = $customer->packages()->first();

        $services = collect($this->getJson(route('api.service'))->json('data'));
        $subDistricts = collect($this->getJson(route('api.geo', ['type' => 'sub_district']))->json('data'));

        $originSubDistrict = $subDistricts->random();
        $destinationSubDistrict = $subDistricts->where('id', '!=', $originSubDistrict['id'])->random();

        $data = [
            'service_code' => $services->random()['code'],
            'sender_name' => $this->faker->name,
            'sender_phone' => $this->faker->phoneNumber,
            'sender_address' => $this->faker->address,
            'receiver_name' => $this->faker->name,
            'receiver_phone' => $this->faker->phoneNumber,
            'receiver_address' => $this->faker->address,
            'origin_regency_id' => $originSubDistrict['regency']['id'],
            'destination_regency_id' => $destinationSubDistrict['regency']['id'],
            'destination_district_id' => $destinationSubDistrict['district']['id'],
            'destination_sub_district_id' => $destinationSubDistrict['id'],
        ];

        $response = $this->putJson(route('api.order.update', ['package_hash' => $package->hash]), $data);

        $response->assertSuccessful();
    }

    public function test_can_get_all_list_order()
    {
        $this->seed(PackagesTableSeeder::class);

        $headers = $this->getCustomersHeader();

        $url = route('api.order');

        $response = $this->getJson($url, $headers);

        $response->assertSuccessful();
    }

    public function test_can_get_order_detail()
    {
        $this->seed(PackagesTableSeeder::class);

        /** @var Customer $customer */
        $customer = Customer::query()->first();

        $this->actingAs($customer);

        /** @var Package $package */
        $package = $customer->packages()->first();

        $url = route('api.order.show', ['package_hash' => $package->hash]);

        $response = $this->getJson($url);

        $response->assertSuccessful();

        $this->assertEquals($package->barcode, $response->json('data.barcode'));
    }
}
