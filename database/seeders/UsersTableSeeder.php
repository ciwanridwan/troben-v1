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
            [
                UserablePivot::ROLE_WAREHOUSE,
                UserablePivot::ROLE_WAREHOUSE,
            ],
            [
                UserablePivot::ROLE_DRIVER,
                UserablePivot::ROLE_DRIVER,
                UserablePivot::ROLE_DRIVER,
            ],
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

        $this->command->info('=> user admin created!');

        $users = collect();

        Partner::factory(count(self::COMPOSES))->create()->map(function (Partner $partner, int $index) use ($admin, $users) {
            if ($index === 0) {
                // make sure at least one record contain admin.
                $admin->partners()->attach($partner, [
                    'role' => UserablePivot::ROLE_OWNER,
                ]);
            }

            $userCreator = function ($role) use (&$index, $partner, $users, &$userCreator) {
                if (is_array($role)) {
                    foreach ($role as $key => $value) {
                        $data = [
                            'username' => Str::slug(strtolower(Partner::getAvailableCodeTypes()[$partner->type].' '.$value)).'-'.$key,
                            'email' => Str::slug(strtolower(Partner::getAvailableCodeTypes()[$partner->type].' '.$value)).'-'.$key.'@trawlbens.co.id',
                            'phone' => '+625555555'.str_pad($users->count(), 3, '0', STR_PAD_LEFT),
                            'verified_at' => Carbon::now(),
                        ];

                        $user = User::factory()->create($data);
                        $partner->users()->attach($user, ['role' => $value]);
                        $users->push($user);
                    }

                    return;
                }

                $data = [
                    'username' => Str::slug(strtolower(Partner::getAvailableCodeTypes()[$partner->type].' '.$role)),
                    'email' => Str::slug(strtolower(Partner::getAvailableCodeTypes()[$partner->type].' '.$role)).'@trawlbens.co.id',
                    'phone' => '+625555555'.str_pad($users->count(), 3, '0', STR_PAD_LEFT),
                    'verified_at' => Carbon::now(),
                ];

                $user = User::factory()->create($data);
                $partner->users()->attach($user, ['role' => $role]);
                $users->push($user);
            };

            collect(self::COMPOSES[$partner->type])->each($userCreator);
        });

        $this->command->info('=> other user created with info : ');
        $this->command->table(
            ['username', 'phone', 'partner', 'role'],
            $users->map(fn (User $user) => [
                $user->username,
                $user->phone,
                $user->partners->pluck('type')->implode(', '),
                $user->partners->pluck('pivot.role')->implode(', '),
            ]));
    }
}
