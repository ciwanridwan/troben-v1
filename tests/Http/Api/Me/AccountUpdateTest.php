<?php

namespace Tests\Http\Api\Me;

use Tests\TestCase;
use App\Models\User;
use Database\Seeders\CustomersTableSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccountUpdateTest extends TestCase
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
        $this->seed(CustomersTableSeeder::class);
        $name = $this->faker->name;

        $response = $this->post(route('api.me.update'), [
            'name' => $name,
        ], $this->getCustomersHeader(null, false));

        $this->assertSuccessResponse($response);

        $this->assertEquals($name, $response->json('data.name'));
        $this->assertEquals($this->verifiedCustomer->email, $response->json('data.email'));
        $this->assertEquals($this->verifiedCustomer->phone, $response->json('data.phone'));
    }
}
