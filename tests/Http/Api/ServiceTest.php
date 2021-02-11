<?php

namespace Tests\Feature\Http\Api;

use App\Http\Response;
use App\Models\Customers\Customer;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ServiceTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_service_all()
    {
        $this->seed();

        $customer = Customer::find(1);
        $access_token = $customer->createToken('test')->plainTextToken;
        $path_name = 'api.service';
        $header = [
            'Authorization' => 'Bearer ' . $access_token,
        ];

        // get all data
        $response = $this->json('GET', route($path_name), [], $header);
        $this->assertSuccessResponse($response);

        // get data by code
        $service_code = Service::all()->random()->first()->getKey();
        $response = $this->json('GET', route($path_name . '.show', ['code' => $service_code]), [], $header);
        $this->assertSuccessResponse($response);

        // get invalid data
        $service_code = Service::all()->random()->first()->getKey();
        $response = $this->json('GET', route($path_name . '.show', ['code' => 'abcd']), [], $header);
        $this->assertResponseWithCode($response, Response::RC_INVALID_DATA);


        // create
        $params = [
            'code' => 'tst',
            'name' => 'test',
            'description' => 'test'
        ];
        $response = $this->json('POST', route($path_name . '.create'), $params, $header);
        $this->assertResponseWithCode($response, Response::RC_CREATED);

        // update
        $service_code = 'tst';
        $params = [
            'name' => 'testing dah',
            'description' => 'test'
        ];
        $response = $this->json('PUT', route($path_name . '.update', ['code' => $service_code]), $params, $header);
        $this->assertResponseWithCode($response, Response::RC_UPDATED);
    }
}
