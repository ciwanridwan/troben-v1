<?php

namespace Tests\Jobs\Payments;

use App\Jobs\Payments\Actions\CreateNewPaymentForDelivery;
use App\Jobs\Payments\Actions\CreateNewPaymentForPackage;
use App\Jobs\Payments\CreateNewPayment;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Package;
use App\Models\Payments\Gateway;
use App\Models\Payments\Payment;
use Database\Seeders\Packages\AssignedPackagesSeeder;
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
        $this->seed(AssignedPackagesSeeder::class);
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
        $gateway = Gateway::where('is_bank_transfer', true)->first();

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

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_on_create_payment_for_package()
    {
        /** @var Package $package */
        $package = Package::all()->random()->first();
        /** @var Authenticatable $customer */
        $customer = $package->customer;
        $this->actingAs($customer);

        /** @var Gateway $gateway */
        $gateway = Gateway::where('is_bank_transfer', true)->first();

        $inputs = [
            'service_type' => Payment::SERVICE_TYPE_PAYMENT,
            'payment_amount' => $package->total_amount,
            'sender_bank' => null,
            'sender_account' => null,
            'sender_name' => $customer->name
        ];

        // $this->expectsJobs(CreateNewPayment::class);
        $job = new CreateNewPaymentForPackage($package, $gateway, $inputs);
        $this->dispatch($job);
        /** @var Payment $payment */
        $payment = $job->payment;
        $this->assertDatabaseHas('payments', array_merge([
            'id' => $payment->id
        ], $inputs));
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_on_create_payment_for_delivery()
    {
        /** @var Delivery $delivery */
        $delivery = Delivery::all()->random()->first();

        /** @var Gateway $gateway */
        $gateway = Gateway::where('is_bank_transfer', true)->first();


        // $this->expectsJobs(CreateNewPaymentForDelivery::class);
        $job = new CreateNewPaymentForDelivery($delivery);
        $this->dispatch($job);

        /** @var Payment $payment */
        $payment = $job->payment;
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'service_type' => Payment::SERVICE_TYPE_DEPOSIT
        ]);
    }
}
