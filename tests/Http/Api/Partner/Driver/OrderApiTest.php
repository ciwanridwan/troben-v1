<?php

namespace Tests\Http\Api\Partner\Driver;

use App\Models\Partners\Pivot\UserablePivot;
use App\Models\User;
use Database\Seeders\Packages\AssignedPackagesSeeder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    public bool $seed = true;

    public function test_can_get_all_order()
    {
        $this->seed(AssignedPackagesSeeder::class);

        /** @var User $user */
        $user = User::query()->whereHas('transporters',
            fn(Builder $builder) => $builder->where('userables.role', UserablePivot::ROLE_DRIVER)->whereHas('deliveries'))->first();

        $this->actingAs($user);

        $uri = route('api.partner.driver.order');

        $response = $this->getJson($uri);

        $response->assertSuccessful();
    }
}
