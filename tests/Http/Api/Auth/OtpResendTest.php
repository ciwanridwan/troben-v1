<?php

namespace Tests\Http\Api\Auth;

use Tests\TestCase;
use App\Http\Response;
use App\Models\Customers\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OtpResendTest extends TestCase
{
    use RefreshDatabase;
//    /**
//     * A basic feature test example.
//     *
//     * @return void
//     */
//    public function test_on_valid_data()
//    {
//        $customer = Customer::factory(1)->create()->first();
//        $otp = $customer->createOtp();
//
//        // get otp id
//        $response = $this->json('POST', route('api.auth.otp.resend'), [
//            'otp' => $otp->getKey(),
//            'retry' => false,
//        ]);
//
//        $this->assertSuccessResponse($response);
//        $this->assertArrayHasKey('otp', $response->original['data']);
//    }
//    /**
//     * A basic feature test example.
//     *
//     * @return void
//     */
//    public function test_on_invalid_data()
//    {
//        $customer = Customer::factory(1)->create()->first();
//        $otp = $customer->createOtp();
//
//        // get otp id
//        $response = $this->json('POST', route('api.auth.otp.resend'), [
//            'otp' => $otp->getKey().'1',
//            'retry' => false,
//        ]);
//        $expected = new Response(Response::RC_INVALID_DATA);
//        $this->assertEquals($expected->code, $response->json('code'));
//    }
//    /**
//     * A basic feature test example.
//     *
//     * @return void
//     */
//    public function test_on_deleted_account()
//    {
//        $customer = Customer::factory(1)->create()->first();
//        $otp = $customer->createOtp();
//        $customer->delete();
//
//        // get otp id
//        $response = $this->json('POST', route('api.auth.otp.resend'), [
//            'otp' => $otp->getKey(),
//            'retry' => false,
//        ]);
//        $expected = new Response(Response::RC_MISMATCH_TOKEN_OWNERSHIP);
//        $this->assertEquals($expected->code, $response->json('code'));
//    }
//    /**
//     * A basic feature test example.
//     *
//     * @return void
//     */
//    public function test_on_missing_data()
//    {
//        $customer = Customer::factory(1)->create()->first();
//        $otp = $customer->createOtp();
//
//        // get otp id
//        $response = $this->json('POST', route('api.auth.otp.resend'), []);
//        $expected = new Response(Response::RC_INVALID_DATA);
//        $this->assertEquals($expected->code, $response->json('code'));
//    }
}
