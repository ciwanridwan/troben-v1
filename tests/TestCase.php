<?php

namespace Tests;

use Carbon\Carbon;
use App\Models\Customers\Customer;
use Illuminate\Testing\TestResponse;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Assert successful response.
     *
     * @param \Illuminate\Testing\TestResponse $response
     */
    public function assertSuccessResponse(TestResponse $response)
    {
        $response->assertOk();

        $response->assertJsonStructure([
            'code',
            'error',
            'message',
            'data',
        ]);

        $this->assertEquals(0, $response->json('code'));
        $this->assertNull($response->json('error'));
    }

    /**
     * Acting as authenticated customer.
     *
     * @return array
     */
    public function getCustomersHeader(): array
    {
        $customer = Customer::factory(1)->create([
            'verified_at' => Carbon::now(),
        ])->first();

        return [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$customer->createToken('phpunit-test')->plainTextToken,
        ];
    }
}
