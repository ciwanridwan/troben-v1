<?php

namespace Tests\Http\Api\Me;

use App\Models\Customers\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UpdateAccountTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    // public function test_user_update_on_valid_data()
    // {
    //     $this->seed();

    //     $valid = User::first();

    //     $response = $this->json('POST', route('api.auth.login'),[
    //         'username' => $valid->phone,
    //         'password' => 'password',
    //         'device_name' => 'phpunit_test',
    //     ], [
    //         'Accept' => 'application/json',
    //     ]);

    //     $this->assertSuccessResponse($response);

    //     $token = $response->original['data']['token'];
        
    //     $response = $this->post(route('api.me.update'),[
    //         'name' => $this->faker->name,
    //     ],[
    //         'Accept' => 'application/json',
    //         'Content-Type' => 'multipart/form-data',
    //         'Authorization' => "Bearer $token",
    //     ]);

    //     $this->assertSuccessResponse($response);
    // }

    public function test_customer_update_on_valid_data()
    {
        $this->seed();

        $valid = Customer::first();

        $response = $this->json('POST', route('api.auth.login'),[
            'username' => $valid->phone,
            'password' => 'password',
            'device_name' => 'phpunit_test',
        ], [
            'Accept' => 'application/json',
        ]);

        $this->assertSuccessResponse($response);

        $token = $response->original['data']['token'];
        
        $response = $this->post(route('api.me.update'),[
            'name' => $this->faker->name,
        ],[
            'Accept' => 'application/json',
            'Content-Type' => 'multipart/form-data',
            'Authorization' => "Bearer $token",
        ]);

        $this->assertSuccessResponse($response);
    }
}
