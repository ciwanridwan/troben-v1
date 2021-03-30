<?php

namespace Tests\Http\Api\Partner;

use Tests\TestCase;
use App\Models\Partners\Partner;
use App\Models\Partners\Pivot\UserablePivot;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AssetApiTest extends TestCase
{
    use RefreshDatabase;

    public bool $seed = true;

    public function test_can_get_all_assets()
    {
        $user = $this->getUser(Partner::TYPE_BUSINESS, UserablePivot::ROLE_OWNER);
        $this->actingAs($user);

        $response = $this->getJson(route('api.partner.asset', ['type' => 'employee']));

        $response->assertSuccessful();
    }
}
