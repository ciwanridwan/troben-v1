<?php

namespace Tests\Http\Api\Order;

use Tests\TestCase;
use App\Models\Packages\Package;
use Illuminate\Http\UploadedFile;
use App\Models\Customers\Customer;
use App\Models\Partners\Transporter;
use App\Casts\Package\Items\Handling;
use Illuminate\Support\Facades\Event;
use Illuminate\Database\Eloquent\Builder;
use App\Jobs\Packages\CustomerUploadReceipt;
use Illuminate\Foundation\Testing\WithFaker;
use App\Events\Packages\PackageApprovedByCustomer;
use Database\Seeders\Packages\PackagesTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Controllers\Api\Order\OrderController;
use Database\Seeders\Packages\AssignedPackagesSeeder;
use Database\Seeders\Packages\CustomerInChargeSeeder;

class CustomerOrderApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public bool $seed = true;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_can_make_an_order()
    {
        $headers = $this->getCustomersHeader();

        $services = collect($this->getJson(route('api.service'), $headers)->json('data'));

        $prices = collect($this->getJson(route('api.pricing'))->json('data'));
        $price = $prices->random();

        $destinationSubDistrict = collect($this->getJson(route('api.geo', [
            'type' => 'sub_district',
            'id' => $price['destination']['id'],
        ]))->json('data'))->first();

        $data = [
            'service_code' => $services->random()['code'],
            'transporter_type' => Transporter::TYPE_BIKE,
            'sender_name' => $this->faker->name,
            'sender_phone' => $this->faker->phoneNumber,
            'sender_address' => $this->faker->address,
            'receiver_name' => $this->faker->name,
            'receiver_phone' => $this->faker->phoneNumber,
            'receiver_address' => $this->faker->address,
            'origin_regency_id' => $price['origin_regency']['id'],
            'destination_regency_id' => $destinationSubDistrict['regency']['id'],
            'destination_district_id' => $destinationSubDistrict['district']['id'],
            'destination_sub_district_id' => $destinationSubDistrict['id'],
            'items' => collect(range(0, 3))->map(fn () => [
                'qty' => $this->faker->numberBetween(1, 3),
                'name' => $this->faker->text(9),
                'weight' => $this->faker->numberBetween(1, 10),
                'width' => $this->faker->numberBetween(1, 40),
                'length' => $this->faker->numberBetween(1, 40),
                'height' => $this->faker->numberBetween(1, 40),
                'price' => $this->faker->randomNumber([19000, 20000, 89999, 180000, 365000]),
                'is_insured' => true,
                'handling' => $this->faker->randomElements(Handling::getTypes()),
            ])->toArray(),
        ];

        $response = $this->postJson(route('api.order.store'), $data, $headers);

        $response->assertSuccessful();
    }

    public function test_can_update_existing_order()
    {
        $this->seed(PackagesTableSeeder::class);

        /** @var Customer $customer */
        $customer = Customer::query()->first();

        $this->actingAs($customer);

        /** @var Package $package */
        $package = $customer->packages()->first();

        $services = collect($this->getJson(route('api.service'))->json('data'));

        $prices = collect($this->getJson(route('api.pricing'))->json('data'));
        $price = $prices->random();

        $destinationSubDistrict = collect($this->getJson(route('api.geo', [
            'type' => 'sub_district',
            'id' => $price['destination']['id'],
        ]))->json('data'))->first();


        $data = [
            'service_code' => $services->random()['code'],
            'sender_name' => $this->faker->name,
            'sender_phone' => $this->faker->phoneNumber,
            'sender_address' => $this->faker->address,
            'receiver_name' => $this->faker->name,
            'receiver_phone' => $this->faker->phoneNumber,
            'receiver_address' => $this->faker->address,
            'handling' => $this->faker->randomElements(Handling::getTypes()),
            'origin_regency_id' => $price['origin_regency']['id'],
            'destination_regency_id' => $destinationSubDistrict['regency']['id'],
            'destination_district_id' => $destinationSubDistrict['district']['id'],
            'destination_sub_district_id' => $destinationSubDistrict['id'],
        ];

        $response = $this->putJson(route('api.order.update', ['package_hash' => $package->hash]), $data);

        $response->assertSuccessful();
    }

    public function test_can_get_all_list_order()
    {
        $this->seed(PackagesTableSeeder::class);

        $headers = $this->getCustomersHeader();

        $url = route('api.order');

        $response = $this->getJson($url, $headers);

        $response->assertSuccessful();
    }

    public function test_can_get_order_detail()
    {
        $this->seed(AssignedPackagesSeeder::class);

        /** @var Customer $customer */
        $customer = Customer::query()->whereHas('packages', fn (Builder $builder) => $builder->where('status', Package::STATUS_WAITING_FOR_PICKUP))->first();

        $this->actingAs($customer);

        /** @var Package $package */
        $package = $customer->packages()->where('status', Package::STATUS_WAITING_FOR_PICKUP)->first();

        $url = route('api.order.show', ['package_hash' => $package->hash]);

        $response = $this->getJson($url);

        $response->assertSuccessful();

        $this->assertEquals($package->barcode, $response->json('data.barcode'));
    }

    public function test_can_approve_order()
    {
        $this->seed(CustomerInChargeSeeder::class);

        /** @var Package $package */
        $package = Package::query()->where('status', Package::STATUS_WAITING_FOR_APPROVAL)->first();

        $this->actingAs($package->customer);

        Event::fake();

        $this->patchJson(action([OrderController::class, 'approve'], ['package_hash' => $package->hash]));

        Event::assertDispatched(PackageApprovedByCustomer::class);
    }

    public function test_can_upload_receipt()
    {
        $this->seed(CustomerInChargeSeeder::class);

        /** @var Package $package */
        $package = Package::query()->where('status', Package::STATUS_WAITING_FOR_APPROVAL)->first();

        $this->actingAs($package->customer);

        $response = $this->patchJson(action([OrderController::class, 'approve'], ['package_hash' => $package->hash]));

        $response->assertOk();

        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'status' => Package::STATUS_ACCEPTED,
        ]);

        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->postJson(action([OrderController::class, 'receipt'], ['package_hash' => $package->hash]), [
            'receipt' => $file,
        ]);

        $response->assertSuccessful();

        $this->assertDatabaseHas('attachments', [
            'title' => $file->getClientOriginalName(),
            'type' => Package::ATTACHMENT_RECEIPT,
            'disk' => CustomerUploadReceipt::DISK_DRIVER,
        ]);

        $this->assertNotNull($package->attachments()->where('type', Package::ATTACHMENT_RECEIPT)->first());
    }
}
