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
        Permission::create(['name' => 'cancel package']);
        Permission::create(['name' => 'verify account']);
        Permission::create(['name' => 'update package']);
        Permission::create(['name' => 'update delivery destination']);
        Permission::create(['name' => 'append package to delivery']);

        // create roles and assign existing permissions
        $role1 = Role::create(['name' => 'super-admin']);
        $role1->givePermissionTo('cancel package');
        $role1->givePermissionTo('verify account');
        $role1->givePermissionTo('update package');
        $role1->givePermissionTo('update delivery destination');
        $role1->givePermissionTo('append package to delivery');

        $role2 = Role::create(['name' => 'operation']);
        $role2->givePermissionTo('cancel package');
        $role2->givePermissionTo('verify account');

        $role3 = Role::create(['name' => 'Super-Admin']);
        // gets all permissions via Gate::before rule; see AuthServiceProvider

        // create demo users
        $user = Office::factory()->create([
            'name' => 'Example User',
            'username' => 'admin_sample',
            'phone' => '+6287885145866',
            'address' => 'Depok 2 Tengah',
            'email' => 'sample@admin.com',
            'password' => 'password',
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        $user->assignRole($role1);

        $user = Office::factory()->create([
            'name' => 'Example User 2',
            'username' => 'admin_sample_2',
            'phone' => '+6287882222866',
            'address' => 'Depok 2 Tengah_2',
            'email' => '2222@admin.com',
            'password' => 'test@example.com',
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        $user->assignRole($role2);

        $user = Office::factory()->create([
            'name' => 'Example User 3',
            'username' => 'admin_sample_33',
            'phone' => '+62878853335866',
            'address' => 'Depok 2 Tengah',
            'email' => '333@admin.com',
            'password' => '333@example.com',
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        $user->assignRole($role3);
    }
}
