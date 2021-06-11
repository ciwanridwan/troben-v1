<?php

namespace Tests\Http\Api\Partner;

use Tests\TestCase;
use App\Models\User;
use App\Models\Partners\Partner;
use App\Models\Partners\Transporter;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Partners\Pivot\UserablePivot;
use Illuminate\Database\Eloquent\Collection;
use Database\Seeders\TransportersTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Controllers\Api\Partner\AssetController;
use libphonenumber\PhoneNumberUtil;

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

    public function test_can_create_employee(): void
    {
        $user = $this->getUser(Partner::TYPE_BUSINESS, UserablePivot::ROLE_OWNER);
        $this->actingAs($user);

        $employeeInputs = User::factory()->makeOne();
        $employeeInputs = $employeeInputs->getAttributes();
        $employeeInputs['password'] = 'password';
        $employeeInputs['password_confirmation'] = 'password';
        $employeeInputs['phone'] = '081234567891';
        $employeeInputs['role'] = [UserablePivot::ROLE_DRIVER];

        $response = $this->postJson(route('api.partner.asset.store', ['type' => 'employee']), $employeeInputs);

        $response->assertSuccessful();

        $loginInputs = [
            'guard' => 'user',
            'username' => $employeeInputs['username'],
            'password' => $employeeInputs['password'],
            'device_name' => 'DEFAULT'
        ];

        $assertEmployee = collect($employeeInputs)->only(['name', 'username', 'email'])->toArray();

        $this->assertDatabaseHas('users', $assertEmployee);

        $response = $this->postJson(route('api.auth.login'), $loginInputs);
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

        /** @var Transporter[]|Collection $inputTransporters */
        $inputTransporters = $partner->transporters()->take(3)->get();
        /** @var User $inputUser */
        $inputUser = $inputTransporters->first()->users()->wherePivot('role', UserablePivot::ROLE_DRIVER)->first();

        $response = $this->patchJson(action([AssetController::class, 'fusion']), [
            'transporter_hashes' => $inputTransporters->pluck('hash')->toArray(),
            'user_hash' => $inputUser->hash,
        ]);

        $response->assertStatus(422);

        $inputTransporters->each(fn (Transporter $transporter) => $transporter->users()->detach($inputUser->id));

        $response = $this->patchJson(action([AssetController::class, 'fusion']), [
            'transporter_hashes' => $inputTransporters->pluck('hash')->toArray(),
            'user_hash' => $inputUser->hash,
        ]);

        $response->assertOk();

        $inputTransporters->each(fn (Transporter $transporter) => $this->assertDatabaseHas('userables', [
            'user_id' => $inputUser->id,
            'userable_type' => Transporter::class,
            'userable_id' => $transporter->id,
            'role' => UserablePivot::ROLE_DRIVER,
        ]));
    }
}
