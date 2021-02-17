<?php

namespace Tests\Http\Api;

use Tests\TestCase;
use App\Models\Price;
use App\Http\Response;
use App\Models\Service;
use App\Models\Geo\SubDistrict;
use App\Models\Customers\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PricingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var array
     */
    protected array $header;

    public function setUp(): void
    {
        parent::setUp();
        // seed all
        $this->seed();
        $customer = Customer::find(1);
        $access_token = $customer->createToken('test')->plainTextToken;
        $this->header = [
            'Authorization' => 'Bearer '.$access_token,
        ];
    }
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_get_price_list()
    {
        $path_name = 'api.pricing';

        // get all data
        $response = $this->json('GET', route($path_name), [], $this->header);
        $this->assertSuccessResponse($response);


        // get all data with destination filter
        // destination is sub_district_id
        $destination_id = SubDistrict::all()->random()->getKey();
        $response = $this->json('GET', route($path_name), [
            'destination_id' => $destination_id,
        ], $this->header);
        $this->assertSuccessResponse($response);

        // get all data with origin filter
        // origin is sub_district_id
        $origin_id = SubDistrict::all()->random()->getKey();
        $response = $this->json('GET', route($path_name), [
            'origin_id' => $origin_id,
        ], $this->header);
        $this->assertSuccessResponse($response);

        // get all data with service filter
        // service is service_code
        // $service_code = Service::all()->random()->getKey();
        $service_code = 'tps';
        $response = $this->json('GET', route($path_name), [
            'service_code' => $service_code,
        ], $this->header);
        $this->assertSuccessResponse($response);


        // get all data with combine filter
        $response = $this->json('GET', route($path_name), [
            'service_code' => $service_code,
            'origin_id' => $origin_id,
            'destination_id' => $destination_id,
        ], $this->header);
        $this->assertSuccessResponse($response);

        // get all data with combine filter
        $params_null = [
            'service_code' => null,
            'origin_id' => null,
            'destination_id' => null,
        ];
        $response = $this->json('GET', route($path_name), $params_null, $this->header);

        // assert is invalid input
        $expected = new Response(Response::RC_INVALID_DATA);
        $this->assertEquals($expected->code, $response->json('code'));

        $errors = $response->json('data');
        foreach ($params_null as $key => $value) {
            $this->assertArrayHasKey($key, $errors);
        }
    }

    public function test_pricing_calculator()
    {
        $price = Price::all()->random();
        // valid q string
        $params = [
            'origin_province_id' => $price->origin_province_id,
            'origin_regency_id' => $price->origin_regency_id,
            'destination_id' => $price->destination_id,
            'height' => 5,
            'width' => 5,
            'length' => 5,
            'weight' => 5,
        ];

        $path_name = 'api.pricing.calculator';
        $response = $this->json('GET', route($path_name), $params);
        $this->assertSuccessResponse($response);

        // valid q string out of range
        $params = [
            'origin_province_id' => $price->origin_province_id,
            'origin_regency_id' => $price->origin_regency_id,
            'destination_id' => 1,
            'height' => 5,
            'width' => 5,
            'length' => 5,
            'weight' => 5,
        ];

        $path_name = 'api.pricing.calculator';
        $response = $this->json('GET', route($path_name), $params);
        $this->assertResponseWithCode($response, Response::RC_OUT_OF_RANGE);

        // missing data
        $path_name = 'api.pricing.calculator';
        $response = $this->json('GET', route($path_name), []);
        $this->assertResponseWithCode($response, Response::RC_INVALID_DATA);

        // invalid data
        $params = [
            'origin_province_id' => 'a',
            'origin_regency_id' => 'a',
            'destination_id' => 'v',
            'height' => 'c',
            'width' => 'c',
            'length' => 'c',
            'weight' => 'c',
        ];
        $path_name = 'api.pricing.calculator';
        $response = $this->json('GET', route($path_name), []);
        $this->assertResponseWithCode($response, Response::RC_INVALID_DATA);
    }
}
