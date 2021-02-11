<?php

namespace Tests\Http\Api\Me;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccountInfoTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_on_valid_data()
    {
        // test using phone number
        $response = $this->get(route('api.me'), $this->getCustomersHeader());
        $this->assertSuccessResponse($response);
    }
}
