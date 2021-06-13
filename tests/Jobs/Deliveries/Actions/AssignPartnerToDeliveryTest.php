<?php

namespace Tests\Jobs\Deliveries\Actions;

use App\Jobs\Deliveries\Actions\AssignPartnerToDelivery;
use App\Models\Deliveries\Delivery;
use App\Models\Partners\Partner;
use Database\Seeders\Packages\InTransit\RequestPartnerSeeder;
use Database\Seeders\Packages\PostPayment\PostPaymentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssignPartnerToDeliveryTest extends TestCase
{
    use RefreshDatabase;

    public bool $seed = true;

    public function test_on_valid_data()
    {
        PostPaymentSeeder::$CUSTOMER_PACKAGE_ITEM_MAX = 2;
        PostPaymentSeeder::$CUSTOMER_PACKAGES = 1;

        $this->seed(RequestPartnerSeeder::class);

        $delivery = Delivery::query()->where('type', Delivery::TYPE_TRANSIT)->where('status', Delivery::STATUS_WAITING_ASSIGN_PARTNER)->first();

        $partner = Partner::query()->where('type', Partner::TYPE_TRANSPORTER)->first();


        $job = new AssignPartnerToDelivery($delivery, $partner);

        dispatch_now($job);

        // get first partner
        $this->assertSame(Delivery::STATUS_WAITING_PARTNER_ASSIGN_TRANSPORTER, $delivery->fresh()->status);
    }
}
