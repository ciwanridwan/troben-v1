<?php

namespace Tests\Http\Api\Auth;

use Carbon\Carbon;
use Tests\TestCase;
use App\Http\Response;
use App\Models\OneTimePassword;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OtpVerificationTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_on_valid_data()
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

        // get otp id
        $otp = OneTimePassword::find($response->original['data']['otp']);
        $response = $this->json('POST', route('api.auth.otp.verify'), [
            'otp' => $otp->getKey(),
            'otp_token' => $otp->token,
            'device_name' => 'Device-test',
        ], [
            'Accept' => 'application/json',
        ]);
        $this->assertSuccessResponse($response);
        $this->assertArrayHasKey('access_token', $response->original['data']);
    }
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_on_mismatch_otp_token()
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

        // get otp id
        $otp = OneTimePassword::find($response->original['data']['otp']);
        $response = $this->json('POST', route('api.auth.otp.verify'), [
            'otp' => $otp->getKey(),
            'otp_token' => '1234',
            'device_name' => 'Device-test',
        ], [
            'Accept' => 'application/json',
        ]);
        $response->assertStatus(422);
        $this->assertEquals($response->original['code'], Response::RC_TOKEN_MISMATCH);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_on_expired_token()
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
        $testNow = Carbon::now()->addHours(20);
        Carbon::setTestNow($testNow);

        // get otp id
        $otp = OneTimePassword::find($response->original['data']['otp']);
        $response = $this->json('POST', route('api.auth.otp.verify'), [
            'otp' => $otp->getKey(),
            'otp_token' => $otp->token,
            'device_name' => 'Device-test',
        ], [
            'Accept' => 'application/json',
        ]);
        $response->assertStatus(422);
        $this->assertEquals($response->original['code'], Response::RC_TOKEN_HAS_EXPIRED);
        Carbon::setTestNow();
    }
}
