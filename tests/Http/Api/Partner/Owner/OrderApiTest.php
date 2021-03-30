<?php

namespace Tests\Http\Api\Partner\Owner;

use Tests\TestCase;
use App\Models\Partners\Partner;
use App\Models\Partners\Pivot\UserablePivot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\Packages\AssignedPackagesSeeder;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    public bool $seed = true;

    public function test_can_get_all_order()
    {
        $this->seed(AssignedPackagesSeeder::class);

        $user = $this->getUser(Partner::TYPE_BUSINESS, UserablePivot::ROLE_OWNER);

        $this->actingAs($user);

        $response = $this->getJson(route('api.partner.owner.order'));

        $response->assertSuccessful();
    }
}
