<?php

namespace Tests\Jobs\OneTimePasswords;

use Tests\TestCase;
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
}
