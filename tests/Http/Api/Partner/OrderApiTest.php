<?php

namespace Tests\Http\Api\Partner;

use App\Models\Partners\Partner;
use App\Models\Partners\Pivot\UserablePivot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    public bool $seed = true;

    public function test_can_get_order_for_business()
    {
        $user = $this->getUser(Partner::TYPE_BUSINESS, UserablePivot::ROLE_OWNER);

        $this->actingAs($user);

        $response = $this->getJson(route('api.partner.order.index'));

        $response->assertOk();
    }
}
