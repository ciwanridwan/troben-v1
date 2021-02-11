<?php

namespace Tests\Jobs\OneTimePasswords;

use Exception;
use Carbon\Carbon;
use Tests\TestCase;
use App\Http\Response;
use App\Exceptions\Error;
use App\Models\Customers\Customer;
use Illuminate\Support\Facades\Event;
use App\Jobs\OneTimePasswords\VerifyOtpToken;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Events\OneTimePasswords\TokenVerified;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VerifyingOtpToken extends TestCase
{
    use RefreshDatabase, DispatchesJobs;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_on_valid_data()
    {
        Event::fake();
        $account = Customer::factory(1)->create()->first();
        $otp = $account->createOtp();
        $job = new VerifyOtpToken($account, $otp, $otp->token);
        $response = $this->dispatch($job);
        $this->assertTrue($response);
        $this->assertTrue($job->account->is_verified);
        $this->assertNotNull($job->otp->claimed_at);
        Event::assertDispatched(TokenVerified::class);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_on_token_ownership_mismatch()
    {
        $this->expectException(Error::class);

        $account = Customer::factory(1)->create()->first();
        $account2 = Customer::factory(1)->create()->first();
        $otp = $account->createOtp();

        $job = new VerifyOtpToken($account2, $otp, $otp->token);
        $this->dispatch($job);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_on_token_mismatch()
    {
        Event::fake();

        try {
            $account = Customer::factory(1)->create()->first();
            $otp = $account->createOtp();
            $job = new VerifyOtpToken($account, $otp, '1234');
            $this->dispatch($job);
        } catch (Exception  $e) {
            $this->assertEquals(new Error(Response::RC_TOKEN_MISMATCH), $e);
        }

        Event::assertNotDispatched(TokenVerified::class);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_on_token_expired()
    {
        Event::fake();

        try {
            $account = Customer::factory(1)->create()->first();
            $otp = $account->createOtp();

            // Set test date to tomorrow
            $testDate = Carbon::tomorrow();
            Carbon::setTestNow($testDate);

            $job = new VerifyOtpToken($account, $otp, $otp->token);
            $this->dispatch($job);

            Carbon::setTestNow();
        } catch (Exception  $e) {
            $this->assertEquals(new Error(Response::RC_TOKEN_HAS_EXPIRED), $e);
        }

        Event::assertNotDispatched(TokenVerified::class);
    }
}
