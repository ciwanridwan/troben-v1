<?php

namespace Tests\Feature\Http\Api;

use App\Models\Customers\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GetAccountInfotest extends TestCase
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

        $valid = Customer::find(1);

        $response = $this->json('POST', route('api.auth.login'), [
            'username' => $valid->phone,
            'password' => 'password',
            'device_name' => 'phpunit_test',
        ], [
            'Accept' => 'application/json',
        ]);
        $this->assertSuccessResponse($response);

        // get token
        $token = $response->original['data']['token'];

        // test using phone number
        $response = $this->get(route('api.me'), [
            'Accept' => 'application/json',
            'CONTENT_TYPE' => 'application/json',
            'Authorization' => 'Bearer ' . $token
        ]);
        $this->assertSuccessResponse($response);
    }
}
