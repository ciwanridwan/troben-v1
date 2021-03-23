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
        Partner::TYPE_TRANSPORTER => [
            UserablePivot::ROLE_OWNER,
            UserablePivot::ROLE_DRIVER,
        ],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'username' => 'admin',
            'phone' => '+625555555555',
            'email' => 'user@trawlbens.co.id',
            'verified_at' => Carbon::now(),
        ]);

        $admin->setAttribute('is_admin', true);
        $admin->save();

        Partner::factory(4)->create()->each(function (Partner $partner, int $index) use ($admin) {
            if ($index === 0) {
                // make sure at least one record contain admin.
                $admin->partners()->attach($partner, [
                    'role' => UserablePivot::ROLE_OWNER,
                ]);
            }

            collect(self::COMPOSES[$partner->type])
                ->each(fn ($role, $key) => $partner
                    ->users()
                    ->attach(User::factory()
                        ->create([
                            'username' => Str::slug(strtolower(Partner::getAvailableCodeTypes()[$partner->type].' '.$role)),
                            'email' => Str::slug(strtolower(Partner::getAvailableCodeTypes()[$partner->type].' '.$role)).'@trawlbens.co.id',
                            'phone' => '+625555555'.str_pad($index.$key, 3, '0', STR_PAD_LEFT),
                            'verified_at' => Carbon::now(),
                        ]), ['role' => $role]));
        });
    }
}
