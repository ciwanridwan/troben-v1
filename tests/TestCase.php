<?php

namespace Tests;

use Carbon\Carbon;
use App\Models\User;
use App\Http\Response;
use App\Models\Customers\Customer;
use Illuminate\Testing\TestResponse;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Verified Customer.
     *
     * @var \App\Models\Customers\Customer|null
     */
    protected ?Customer $verifiedCustomer;

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

        self::assertEquals(0, $response->json('code'));
        self::assertNull($response->json('error'));
    }

    /**
     * @param TestResponse $response
     */
    public function assertAccountNotVerifiedResponse(TestResponse $response)
    {
        $response->assertOk();

        $response->assertJsonStructure([
            'code',
            'error',
            'message',
            'data',
        ]);

        self::assertEquals(0203, $response->json('code'));
        self::assertNull($response->json('error'));
    }

    /**
     * Acting as authenticated customer.
     *
     * @param \App\Models\Customers\Customer|null $customer
     * @param bool                                $withFactory
     *
     * @return array
     */
    public function getCustomersHeader(?Customer $customer = null, $withFactory = true): array
    {
        if (is_a($customer, Customer::class)) {
            $this->verifiedCustomer = $customer;
        }

        if (is_null($customer)) {
            $customer = $this->makeVerifiedCustomer($withFactory);
        }

        return [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$customer->createToken('phpunit-test')->plainTextToken,
        ];
    }

    public function assertResponseWithCode(TestResponse $response, string $code)
    {
        $expected = new Response($code);

        self::assertEquals($expected->code, $response->json('code'));
    }

    public function getUser($types, string $roles, ?\Closure $additionalQuery = null): ?User
    {
        $query = User::partnerRole($types, $roles);

        $query->when($additionalQuery, $additionalQuery);

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $query->first();
    }

    /**
     * Get Verified Customer.
     *
     * @param bool $withFactory
     *
     * @return \App\Models\Customers\Customer
     */
    protected function makeVerifiedCustomer($withFactory = true): Customer
    {
        $this->verifiedCustomer = ($withFactory)
            ? Customer::factory(1)->create(['phone_verified_at' => Carbon::now(), 'phone' => '+625555555555'])->first()
            : Customer::query()->whereNotNull('phone_verified_at')->first();

        return $this->verifiedCustomer;
    }
}
