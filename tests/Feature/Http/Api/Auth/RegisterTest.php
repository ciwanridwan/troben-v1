<?php

namespace Tests\Feature\Http\Api\Auth;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_any_phone_number_format_successful_register()
    {
        // test using any format number phone
        $response = $this->json('POST', route('api.auth.register'), [
            'name' => 'username test',
            'phone' => '08512345679',
            'password' => 'aLphAnumeric123',
            'email' => 'email@test.com',
        ], [
            'Accept' => 'application/json',
        ]);
        $this->assertSuccessResponse($response);
    }

    public function test_e164_phone_number_format_successful_register()
    {
        // test using any format number phone
        $response = $this->json('POST', route('api.auth.register'), [
            'name' => 'username test',
            'phone' => '+628512345679',
            'password' => 'aLphAnumeric123',
            'email' => 'email@test.com',
        ], [
            'Accept' => 'application/json',
        ]);
        $this->assertSuccessResponse($response);
    }


    public function test_alphanumeric_password_successful_register()
    {
        // test using alphanumeric password
        $response = $this->json('POST', route('api.auth.register'), [
            'name' => 'username test',
            'phone' => '+628512345679',
            'password' => 'password1234',
            'email' => 'email@test.com',
        ], [
            'Accept' => 'application/json',
        ]);
        $this->assertSuccessResponse($response);
    }

    public function test_alphabet_password_successful_register()
    {
        // test using alphabet password
        $response = $this->json('POST', route('api.auth.register'), [
            'name' => 'username test',
            'phone' => '+628512345679',
            'password' => 'password',
            'email' => 'email@test.com',
        ], [
            'Accept' => 'application/json',
        ]);
        $this->assertSuccessResponse($response);
    }

    public function test_numeric_password_successful_register()
    {

        // test using numeric password
        $response = $this->json('POST', route('api.auth.register'), [
            'name' => 'username test',
            'phone' => '+628512345679',
            'password' => '123456789',
            'email' => 'email@test.com',
        ], [
            'Accept' => 'application/json',
        ]);
        $this->assertSuccessResponse($response);
    }

    public function test_missing_data_register()
    {
        // test using numeric password
        $response = $this->json('POST', route('api.auth.register'), [], [
            'Accept' => 'application/json',
        ]);
        $response->assertStatus(422);
    }

    public function test_invalid_data_register()
    {
        // test using numeric password
        $response = $this->json('POST', route('api.auth.register'), [
            'name' => 'username test',
            'phone' => '+62555555555555',
            'password' => '123456789',
            'email' => 'email@test.com',
        ], [
            'Accept' => 'application/json',
        ]);
        $response->assertStatus(422);
    }
}
