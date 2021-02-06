<?php

namespace Tests\Jobs\OneTimePasswords;

use App\Jobs\OneTimePasswords\VerifyOtpToken;
use App\Models\Customers\Customer;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

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
        $this->seed();

        $account = Customer::find(1);
        $otp = $account->createOtp();
        $job = new VerifyOtpToken($otp, $otp->token);
        $response = $this->dispatch($job);
        $this->assertTrue($response);
        $this->assertTrue($job->account->is_verified);
        $this->assertNotNull($job->otp->claimed_at);
    }
}
