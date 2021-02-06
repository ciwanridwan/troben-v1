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
        // Event::fake();
        $account = Customer::factory(1)->create()->first();
        $otp = $account->createOtp();
        $job = new VerifyOtpToken($account, $otp, $otp->token);
        $response = $this->dispatch($job);
        $this->assertTrue($response);
        $this->assertTrue($job->account->is_verified);
        $this->assertNotNull($job->otp->claimed_at);
        // Event::assertDispatched(TokenVerified::class);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_on_token_ownership_missmatch()
    {
        try {
            // Event::fake();
            $account = Customer::factory(1)->create()->first();
            $account2 = Customer::factory(1)->create()->first();
            $otp = $account->createOtp();
            $job = new VerifyOtpToken($account2, $otp, $otp->token);
            $response = $this->dispatch($job);
            // Event::assertDispatched(TokenVerified::class);
        } catch (Exception  $e) {
            $this->assertEquals(new Error(Response::RC_MISSMATCH_TOKEN_OWNERSHIP), $e);
        }
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_on_token_missmatch()
    {
        try {
            // Event::fake();
            $account = Customer::factory(1)->create()->first();
            $otp = $account->createOtp();
            $job = new VerifyOtpToken($account, $otp, '1234');
            $response = $this->dispatch($job);
            // Event::assertDispatched(TokenVerified::class);
        } catch (Exception  $e) {
            $this->assertEquals(new Error(Response::RC_TOKEN_MISSMATCH), $e);
        }
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_on_token_expired()
    {
        try {

            // Event::fake();
            $account = Customer::factory(1)->create()->first();
            $otp = $account->createOtp();

            // Set test date to tomorrow
            $testDate = Carbon::tomorrow();
            Carbon::setTestNow($testDate);

            $job = new VerifyOtpToken($account, $otp, $otp->token);
            $this->dispatch($job);

            Carbon::setTestNow();
            // Event::assertDispatched(TokenVerified::class);
        } catch (Exception  $e) {
            $this->assertEquals(new Error(Response::RC_TOKEN_HAS_EXPIRED), $e);
        }
    }
}
