<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use App\Models\Partners\Partner;
use App\Models\Partners\Transporter;
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

        $this->command->info('=> user admin created!');

        $users = Partner::factory(4)->create()->map(function (Partner $partner, int $index) use ($admin) {
            if ($index === 0) {
                // make sure at least one record contain admin.
                $admin->partners()->attach($partner, [
                    'role' => UserablePivot::ROLE_OWNER,
                ]);
            }

            /** @var Transporter|null $transporter */
            $transporter = null;

            if ($partner->type === Partner::TYPE_TRANSPORTER || collect(self::COMPOSES[$partner->type])->contains(UserablePivot::ROLE_DRIVER)) {
                $transporter = Transporter::factory()->state(['partner_id' => $partner->id])->create();
            }

            return collect(self::COMPOSES[$partner->type])
                ->map(function ($role, $key) use ($index, $partner, $transporter) {
                    $data = [
                        'username' => Str::slug(strtolower(Partner::getAvailableCodeTypes()[$partner->type].' '.$role)),
                        'email' => Str::slug(strtolower(Partner::getAvailableCodeTypes()[$partner->type].' '.$role)).'@trawlbens.co.id',
                        'phone' => '+625555555'.str_pad($index.$key, 3, '0', STR_PAD_LEFT),
                        'verified_at' => Carbon::now(),
                    ];

                    $user = User::factory()->create($data);

                    $partner->users()->attach($user, ['role' => $role]);

                    if ($transporter && in_array($role, [UserablePivot::ROLE_DRIVER, UserablePivot::ROLE_OWNER])) {
                        $transporter->users()->attach($user, ['role' => $role]);
                    }

                    return $user;
                });
        })->flatten(1);

        $this->command->info('=> other user created with info : ');
        $this->command->table(
            ['username', 'email', 'phone', 'partner', 'role', 'transporter'],
            $users->map(fn (User $user) => [
                $user->username,
                $user->email,
                $user->phone,
                $user->partners->pluck('type')->implode(', '),
                $user->partners->pluck('pivot.role')->implode(', '),
                $user->transporters->map(fn (Transporter $transporter) => $transporter->registration_number.':'.$transporter->pivot->role)->implode(', '),
            ]));
    }
}
