<?php

namespace Tests\Feature\Http\Api\Auth;

use Tests\TestCase;
use App\Models\Customers\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_attempt()
    {
        $this->seed();

        $valid = Customer::find(1);
        // test using phone number
        $response = $this->json('POST', route('api.auth.login'), [
            'username' => $valid->phone,
            'password' => 'password',
            'device_name' => 'phpunit_test',
        ], [
            'Accept' => 'application/json',
        ]);

        $this->assertSuccessResponse($response);

        // test using email
        $response = $this->json('POST', route('api.auth.login'), [
            'username' => $valid->email,
            'password' => 'password',
            'device_name' => 'phpunit_test',
        ], [
            'Accept' => 'application/json',
        ]);

        $this->assertSuccessResponse($response);
    }
}
