<?php

namespace Tests\Http\Api\Partner\Warehouse;

use App\Models\Partners\Partner;
use App\Models\Partners\Pivot\UserablePivot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    public bool $seed = true;

    public function test_can_get_list_order()
    {
        $user = $this->getUser(Partner::TYPE_BUSINESS, UserablePivot::ROLE_WAREHOUSE);

        $this->actingAs($user);

        $uri = route('api.partner.warehouse.order');

        $response = $this->getJson($uri);

        $response->assertOk();
    }
}
