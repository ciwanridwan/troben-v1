<?php

namespace Tests\Jobs\Payments;

use App\Jobs\Payments\CreateNewPayment;
use App\Models\Packages\Package;
use App\Models\Payments\Gateway;
use App\Models\Payments\Payment;
use Database\Seeders\Packages\PackagesTableSeeder;
use Database\Seeders\PaymentGatewaySeeder;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateNewPaymentTest extends TestCase
{
    use RefreshDatabase, DispatchesJobs;

    public bool $seed = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PackagesTableSeeder::class);
        $this->seed(PaymentGatewaySeeder::class);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_on_create_payment()
    {
        /** @var Package $package */
        $package = Package::all()->random()->first();
        /** @var Authenticatable $customer */
        $customer = $package->customer;
        $this->actingAs($customer);

        /** @var Gateway $gateway */
        $gateway = Gateway::where('is_bank_transfer', false)->first();

        $inputs = [
            'service_type' => Payment::SERVICE_TYPE_PAYMENT,
            'payment_amount' => $package->total_amount,
            'sender_bank' => null,
            'sender_account' => null,
            'sender_name' => $customer->name
        ];

        // $this->expectsJobs(CreateNewPayment::class);
        $job = new CreateNewPayment($package, $gateway, $inputs);
        $this->dispatch($job);
        /** @var Payment $payment */
        $payment = $job->payment;
        $this->assertDatabaseHas('payments', array_merge([
            'id' => $payment->id
        ], $inputs));
    }
}