<?php

namespace Tests\Http\Api\Partner;

use App\Models\Partners\Transporter;
use App\Models\User;
use Database\Seeders\TransportersTableSeeder;
use Illuminate\Database\Eloquent\Builder;
use Tests\TestCase;
use App\Models\Partners\Partner;
use App\Models\Partners\Pivot\UserablePivot;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AssetApiTest extends TestCase
{
    use RefreshDatabase;

    public bool $seed = true;

    public function test_can_get_all_assets(): void
    {
        $user = $this->getUser(Partner::TYPE_BUSINESS, UserablePivot::ROLE_OWNER);
        $this->actingAs($user);

        $response = $this->getJson(route('api.partner.asset', ['type' => 'employee']));

        $response->assertSuccessful();
    }

    public function test_can_fusion_user_to_transporter(): void
    {
        $this->seed(TransportersTableSeeder::class);

        $user = $this->getUser(
            Partner::TYPE_BUSINESS,
            UserablePivot::ROLE_OWNER,
            fn (Builder $builder) => $builder->whereHas('partners', fn (Builder $builder) => $builder->whereHas('transporters'))
        );

        /** @var Partner $partner */
        $partner = $user->partners()->first();
        $this->actingAs($user);

        /** @var Transporter $inputTransporter */
        $inputTransporter = $partner->transporters()->first();
        /** @var User $inputUser */
        $inputUser = $inputTransporter->users()->wherePivot('role', UserablePivot::ROLE_DRIVER)->first();

        $response = $this->patchJson(route('api.partner.asset.fusion'), [
            'transporter_hash' => $inputTransporter->hash,
            'user_hash' => $inputUser->hash,
        ]);

        $response->assertJsonValidationErrors('user_hash', 'data');

        $inputTransporter->users()->detach($inputUser->id);

        $response = $this->patchJson(route('api.partner.asset.fusion'), [
            'transporter_hash' => $inputTransporter->hash,
            'user_hash' => $inputUser->hash,
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('userables', [
            'user_id' => $inputUser->id,
            'userable_type' => Transporter::class,
            'userable_id' => $inputTransporter->id,
            'role' => UserablePivot::ROLE_DRIVER,
        ]);
    }
}
