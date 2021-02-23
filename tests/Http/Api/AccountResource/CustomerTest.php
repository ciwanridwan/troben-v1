<?php

namespace Tests\Http\Api\AccountResource;

use Tests\TestCase;
use App\Models\User;
use App\Http\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_on_valid_data()
    {
        $this->seed();
        // test on valid data
        $params = [
            'name' => 'di',
            'email' => 'com',
            'phone' => '1',
            'q' => 'com',
        ];
        $admin = User::where('username', 'admin')->first();
        $token = $admin->createToken('TEST')->plainTextToken;
        $headers = [
            'Authorization' => 'Bearer '.$token,
        ];
        $route_name = 'api.admin.master.customer';

        $response = $this->json('GET', route($route_name), $params, $headers);
        $this->assertResponseWithCode($response, Response::RC_SUCCESS);

        // test on invalid data
        $params = [
            'name' => '',
            'email' => '',
            'phone' => '',
            'q' => '',
        ];
        $response = $this->json('GET', route($route_name), $params, $headers);
        $this->assertResponseWithCode($response, Response::RC_INVALID_DATA);

        // test on missing data or get all data
        $response = $this->json('GET', route($route_name), [], $headers);
        $this->assertResponseWithCode($response, Response::RC_SUCCESS);
    }
}