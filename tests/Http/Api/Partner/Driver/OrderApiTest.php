<?php

namespace Tests\Http\Api\Partner\Driver;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\Packages\AssignedPackagesSeeder;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    public bool $seed = true;

    public function test_can_get_all_order()
    {
        $this->seed(AssignedPackagesSeeder::class);

        /** @var User $user */
        $user = User::query()->whereHas('deliveries')->first();

        $this->actingAs($user);

        $uri = route('api.partner.driver.order');

        $response = $this->getJson($uri);

        $response->assertSuccessful();
    }
}
