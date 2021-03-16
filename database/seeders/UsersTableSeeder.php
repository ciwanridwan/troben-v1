<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use App\Models\Partners\Partner;
use App\Models\Partners\Pivot\UserablePivot;

class UsersTableSeeder extends Seeder
{
    const COMPOSES = [
        Partner::TYPE_BUSINESS => [
            UserablePivot::ROLE_OWNER,
            UserablePivot::ROLE_WAREHOUSE,
            UserablePivot::ROLE_DRIVER,
            UserablePivot::ROLE_CS,
            UserablePivot::ROLE_CASHIER,
        ],
        Partner::TYPE_SPACE => [
            UserablePivot::ROLE_OWNER,
            UserablePivot::ROLE_WAREHOUSE,
            UserablePivot::ROLE_CS,
            UserablePivot::ROLE_CASHIER,
        ],
        Partner::TYPE_POOL => [
            UserablePivot::ROLE_OWNER,
            UserablePivot::ROLE_WAREHOUSE,
        ],
        Partner::TYPE_TRANSPORTER => [],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Partner::factory(3)->create()->each(function (Partner $partner, int $index) {
            if ($index === 0) {
                // make sure at least one record contain this data.

                /** @var User $user */
                $user = User::factory()->create([
                    'username' => 'admin',
                    'phone' => '+625555555555',
                    'email' => 'user@trawlbens.co.id',
                    'verified_at' => Carbon::now(),
                ]);

                $user->partners()->attach($partner, [
                    'role' => UserablePivot::ROLE_OWNER,
                ]);
            }

            collect(self::COMPOSES[$partner->type])
                ->each(fn ($role) => $partner
                    ->users()
                    ->attach(User::factory()
                        ->create([
                            'username' => Str::slug(strtolower(Partner::getAvailableCodeTypes()[$partner->type].' '.$role)),
                        ]), ['role' => $role]));
        });
    }
}
