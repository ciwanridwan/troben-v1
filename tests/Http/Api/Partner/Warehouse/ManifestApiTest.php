<?php

namespace Tests\Http\Api\Partner\Warehouse;

use App\Http\Controllers\Api\Partner\ManifestController as PartnerManifestController;
use Tests\TestCase;
use App\Models\Code;
use App\Models\User;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use App\Models\Deliveries\Delivery;
use App\Models\Deliveries\Deliverable;
use Database\Seeders\UsersTableSeeder;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Partners\Pivot\UserablePivot;
use Database\Seeders\TransportersTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\Packages\PostPayment\PackedSeeder;
use Database\Seeders\Packages\PostPayment\ManifestSeeder;
use Database\Seeders\Packages\PostPayment\PostPaymentSeeder;
use App\Http\Controllers\Api\Partner\Warehouse\ManifestController;
use App\Http\Controllers\Api\Partner\Warehouse\Manifest\AssignableController;
use App\Http\Controllers\Api\Partner\Warehouse\Manifest\AssignationController;

class ManifestApiTest extends TestCase
{
    use RefreshDatabase;

    public const PARTNER_COUNT = 3;

    public bool $seed = true;

    protected function setUp(): void
    {
        UsersTableSeeder::$COUNT = self::PARTNER_COUNT;

        parent::setUp();
    }

    public function test_can_get_partner_for_manifest(): void
    {
        $user = $this->getUser(Partner::TYPE_BUSINESS, UserablePivot::ROLE_WAREHOUSE);
        $this->actingAs($user);

        $response = $this->getJson(action([AssignableController::class, 'partner']));

        $response->assertOk();

        // can filter
        $response = $this->getJson(action([AssignableController::class, 'partner'], [
            'type' => Partner::TYPE_BUSINESS,
        ]));

        $response->assertOk();
        $response->assertJsonCount(self::PARTNER_COUNT - 1, 'data');
    }

    public function test_can_get_transporter_driver_for_manifest(): void
    {
        $this->seed(TransportersTableSeeder::class);

        $user = $this->getUser(
            Partner::TYPE_BUSINESS,
            UserablePivot::ROLE_WAREHOUSE,
            fn (Builder $builder) => $builder->whereHas(
                'partners',
                fn (Builder $builder) => $builder->whereHas('transporters')
            )
        );

        $this->actingAs($user);

        $response = $this->getJson(action([AssignableController::class, 'driver']));

        $response->assertOk();
        self::assertNotEmpty($response->json('data'));
        $response->assertJsonStructure([
            'data' => [
                [
                    'hash',
                    'role',
                    'user',
                    'transporter',
                ],
            ],
        ]);
    }

    public function test_can_get_package_for_manifest(): void
    {
        $this->seed(PackedSeeder::class);

        /** @var Package $package */
        $package = Package::query()->where('status', Package::STATUS_PACKED)->first();

        /** @var User $user */
        $user = User::query()->find($package->packager_id);
        $this->actingAs($user);

        $response = $this->getJson(action([AssignableController::class, 'package']));

        $response->assertOk();
    }

    public function test_can_create_manifest(): void
    {
        $this->seed(PostPaymentSeeder::class);

        $user = $this->getUser(
            Partner::TYPE_BUSINESS,
            UserablePivot::ROLE_WAREHOUSE,
            fn (Builder $builder) => $builder->whereHas(
                'partners',
                fn (Builder $builder) => $builder->whereHas('deliveries')
            )
        );

        $this->actingAs($user);

        $targetPartnerHash = $this->getJson(action([AssignableController::class, 'partner'], [
            'type' => Partner::TYPE_BUSINESS,
        ]))->json('data.0.hash');

        $response = $this->postJson(action([ManifestController::class, 'store']), [
            'target_partner_hash' => $targetPartnerHash,
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('deliveries', [
            'partner_id' => Partner::hashToId($targetPartnerHash),
            'status' => Delivery::STATUS_WAITING_ASSIGN_PACKAGE,
        ]);

        $response = $this->getJson(action([ManifestController::class, 'index']));

        $response->assertJsonCount(1, 'data');

        self::assertSame(Delivery::AS_ORIGIN, $response->json('data.0.as'));
    }

    public function test_can_scan_package_to_manifest(): void
    {
        $this->seed(ManifestSeeder::class);

        /** @var Package $package */
        $package = Package::query()->where('status', Package::STATUS_PACKED)->first();

        /** @var User $user */
        $user = User::query()->find($package->packager_id);
        $this->actingAs($user);

        $response = $this->getJson(action([ManifestController::class, 'index']));

        $response->assertOk();
        self::assertSame(Delivery::STATUS_WAITING_ASSIGN_PACKAGE, $response->json('data.0.status'));

        $deliveryHash = $response->json('data.0.hash');

        $response = $this->patchJson(action([AssignationController::class, 'package'], ['delivery_hash' => $deliveryHash]), [
            'code' => $package->item_codes->pluck('content')->toArray(),
        ]);


        $response->assertOk();

        $delivery = Delivery::byHash($deliveryHash);

        self::assertNotEmpty($delivery->item_codes);

        self::assertSame($package->item_codes()->count(), $delivery->item_codes()->count());

        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'status' => Package::STATUS_MANIFESTED,
        ]);

        $itemCode = $package->item_codes()->first();

        $response = $this->patchJson(action([AssignationController::class, 'package'], ['delivery_hash' => $deliveryHash]), [
            'code' => $itemCode->content,
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('deliverables', [
            'delivery_id' => $delivery->id,
            'deliverable_type' => Code::class,
            'deliverable_id' => $itemCode->id,
            'status' => Deliverable::STATUS_PREPARED_BY_ORIGIN_WAREHOUSE,
        ]);

        $response = $this->getJson(action([AssignableController::class, 'package']));

        $packagesCodes = collect($response->json('data'))->map->code->map->content;

        $response = $this->patchJson(action([AssignationController::class, 'package'], ['delivery_hash' => $deliveryHash]), [
            'code' => $packagesCodes->random(1)->first(),
        ]);

        $response->assertOk();

        $response = $this->getJson(action([PartnerManifestController::class, 'show'], ['delivery_hash' => $deliveryHash]));

        $response->assertOk();
    }
}
