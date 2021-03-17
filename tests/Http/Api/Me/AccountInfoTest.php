<?php

namespace Tests\Http\Api\Me;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccountInfoTest extends TestCase
{
    use RefreshDatabase;

    public bool $seed = true;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_on_valid_data_customer()
    {
        // test using phone number
        $response = $this->get(route('api.me'), $this->getCustomersHeader());
        $this->assertSuccessResponse($response);
    }

    public function test_on_valid_data_user()
    {
        /** @var User $user */
        $user = User::query()->first();

        $this->actingAs($user);

        $response = $this->get(route('api.me'));

        $response->assertSuccessful();
    }
}
