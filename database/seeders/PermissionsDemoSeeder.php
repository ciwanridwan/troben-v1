<?php

namespace Database\Seeders;

use App\Models\Offices\Office;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsDemoSeeder extends Seeder
{
    /**
     * Create the initial roles and permissions.
     *
     * @return void
     */
    public function run()
    {
        // create permissions
        Permission::create(['name' => 'cancel-package', 'description' => 'cancel-package']);
        Permission::create(['name' => 'verify-account', 'description' => 'verify-account']);
        Permission::create(['name' => 'update-package', 'description' => 'update-package']);
        Permission::create(['name' => 'update-delivery-destination', 'description' => 'update-delivery-destination']);
        Permission::create(['name' => 'append-package-to-delivery', 'description' => 'append-package-to-delivery']);

        // create roles and assign existing permissions
        $role1 = Role::create(['name' => 'super-admin']);
        $role1->givePermissionTo('cancel-package');
        $role1->givePermissionTo('verify-account');
        $role1->givePermissionTo('update-package');
        $role1->givePermissionTo('update-delivery-destination');
        $role1->givePermissionTo('append-package-to-delivery');

        $role2 = Role::create(['name' => 'operation']);
        $role2->givePermissionTo('cancel-package');
        $role2->givePermissionTo('update-package');
        $role2->givePermissionTo('verify-account');

        $role3 = Role::create(['name' => 'tracking']);
        $role3->givePermissionTo('update-delivery-destination');
        $role3->givePermissionTo('append-package-to-delivery');

        // gets all permissions via Gate::before rule; see AuthServiceProvider

        // create demo users
        $user = Office::factory()->create([
            'name' => 'Super Admin',
            'username' => 'super_admin',
            'phone' => '+6287885145866',
            'address' => 'Depok 2 Tengah',
            'email' => 'super@trawlbens.com',
            'password' => 'password',
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        $user->assignRole($role1);

        $user = Office::factory()->create([
            'name' => 'Operation',
            'username' => 'operation_trawlbens',
            'phone' => '+6282246004103',
            'address' => 'Jl. Kebon Jeruk Nomor 45, Jakarta Barat',
            'email' => 'operation@trawlbens.com',
            'password' => 'password',
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        $user->assignRole($role2);

        $user = Office::factory()->create([
            'name' => 'Tracking',
            'username' => 'tracking_trawlbens',
            'phone' => '+6284456781234',
            'address' => 'Jl. Rasamala No.35, Jakarta Selatan',
            'email' => 'tracking@trawlbens.com',
            'password' => 'password',
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        $user->assignRole($role3);
    }
}
