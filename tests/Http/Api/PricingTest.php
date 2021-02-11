<?php

namespace Tests\Http\Api;

use App\Http\Response;
use App\Models\Customers\Customer;
use App\Models\Geo\SubDistrict;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PricingTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_get_price_list()
    {
        // seed all
        $this->seed();

        $customer = Customer::find(1);
        $access_token = $customer->createToken('test')->plainTextToken;
        $path_name = 'api.pricing';
        $header = [
            'Authorization' => "Bearer " . $access_token
        ];

        // get all data
        $response = $this->json('GET', route($path_name), [], $header);
        $this->assertSuccessResponse($response);


        // get all data with destination filter
        // destination is sub_district_id
        $destination_id = SubDistrict::all()->random()->getKey();
        $response = $this->json('GET', route($path_name), [
            'destination_id' => $destination_id
        ], $header);
        $this->assertSuccessResponse($response);

        // get all data with origin filter
        // origin is sub_district_id
        $origin_id = SubDistrict::all()->random()->getKey();
        $response = $this->json('GET', route($path_name), [
            'origin_id' => $origin_id
        ], $header);
        $this->assertSuccessResponse($response);

        // get all data with service filter
        // service is service_code
        // $service_code = Service::all()->random()->getKey();
        $service_code = 'tps';
        $response = $this->json('GET', route($path_name), [
            'service_code' => $service_code
        ], $header);
        $this->assertSuccessResponse($response);


        // get all data with combine filter
        $response = $this->json('GET', route($path_name), [
            'service_code' => $service_code,
            'origin_id' => $origin_id,
            'destination_id' => $destination_id
        ], $header);
        $this->assertSuccessResponse($response);

        // get all data with combine filter
        $params_null = [
            'service_code' => null,
            'origin_id' => null,
            'destination_id' => null
        ];
        $response = $this->json('GET', route($path_name), $params_null, $header);

        // assert is invalid input
        $expected = new Response(Response::RC_INVALID_DATA);
        $this->assertEquals($expected->code, $response->json('code'));

        $errors = $response->json('data');
        foreach ($params_null as $key => $value) {
            $this->assertArrayHasKey($key, $errors);
        }
    }
}
