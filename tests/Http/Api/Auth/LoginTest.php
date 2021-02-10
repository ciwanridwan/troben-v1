<?php

namespace Tests\Http\Api\Auth;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_attempt()
    {
        $this->makeVerifiedCustomer();
        $this->assertDatabaseCount('customers', 1);

        // test using phone number
        $response = $this->json('POST', route('api.auth.login'), [
            'username' => $this->verifiedCustomer->phone,
            'password' => 'password',
            'device_name' => 'phpunit_test',
        ], [
            'Accept' => 'application/json',
        ]);
        $this->assertSuccessResponse($response);

        // test using email
        $response = $this->json('POST', route('api.auth.login'), [
            'username' => $this->verifiedCustomer->email,
            'password' => 'password',
            'device_name' => 'phpunit_test',
        ], [
            'Accept' => 'application/json',
        ]);

        $this->assertSuccessResponse($response);
    }
}
