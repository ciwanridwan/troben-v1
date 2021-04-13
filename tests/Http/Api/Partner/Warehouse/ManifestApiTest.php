<?php

namespace Tests\Http\Api\Partner\Warehouse;

use App\Http\Controllers\Api\Partner\Warehouse\Manifest\AssignableController;
use App\Models\Packages\Package;
use App\Models\User;
use Database\Seeders\Packages\PostPayment\PackedSeeder;
use Database\Seeders\TransportersTableSeeder;
use Tests\TestCase;
use App\Models\Partners\Partner;
use App\Models\Deliveries\Delivery;
use Database\Seeders\UsersTableSeeder;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Partners\Pivot\UserablePivot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\Packages\PostPayment\PostPaymentSeeder;
use App\Http\Controllers\Api\Partner\Warehouse\ManifestController;

class ManifestApiTest extends TestCase
{
    use RefreshDatabase;

    const PARTNER_COUNT = 3;

    public bool $seed = true;

    protected function setUp(): void
    {
        UsersTableSeeder::$COUNT = self::PARTNER_COUNT;

        parent::setUp();
    }

    public function test_can_get_partner_for_manifest()
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

    public function test_can_get_transporter_driver_for_manifest()
    {
        $this->seed(TransportersTableSeeder::class);

        $user = $this->getUser(Partner::TYPE_BUSINESS, UserablePivot::ROLE_WAREHOUSE,
            fn (Builder $builder) => $builder->whereHas('partners',
                fn (Builder $builder) => $builder->whereHas('transporters')));

        $this->actingAs($user);

        $response = $this->getJson(action([AssignableController::class, 'driver']));

        $response->assertOk();
        $this->assertNotEmpty($response->json('data'));
        $response->assertJsonStructure([
            'data' => [
                [
                    'hash',
                    'role',
                    'user',
                    'transporter',
                ]
            ]
        ]);
    }

    public function test_can_get_package_for_manifest()
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

    public function test_can_create_manifest()
    {
        $this->seed(PostPaymentSeeder::class);

        $user = $this->getUser(Partner::TYPE_BUSINESS, UserablePivot::ROLE_WAREHOUSE,
            fn (Builder $builder) => $builder->whereHas('partners',
                fn (Builder $builder) => $builder->whereHas('deliveries')));

        $this->actingAs($user);

        $targetPartnerHash = $this->getJson(action([AssignableController::class, 'partner'], [
            'type' => Partner::TYPE_BUSINESS,
        ]))->json('data.0.hash');

        $response = $this->postJson(action([ManifestController::class, 'store']), [
            'target_partner_hash' => $targetPartnerHash,
        ]);

        $response->assertOk();

        $response = $this->getJson(action([ManifestController::class, 'index']));

        $response->assertJsonCount(1, 'data');

        $this->assertSame(Delivery::AS_ORIGIN, $response->json('data.0.as'));
    }
}
