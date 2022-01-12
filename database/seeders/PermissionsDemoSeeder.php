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
//        Permission::create(['name' => 'cancel-package', 'description' => 'cancel-package']);
//        Permission::create(['name' => 'verify-account', 'description' => 'verify-account']);
//        Permission::create(['name' => 'update-package', 'description' => 'update-package']);
//        Permission::create(['name' => 'update-delivery-destination', 'description' => 'update-delivery-destination']);
//        Permission::create(['name' => 'append-package-to-delivery', 'description' => 'append-package-to-delivery']);

        // create roles and assign existing permissions
        $role1 = Role::create(['name' => 'super-admin']);
//        $role1->givePermissionTo('cancel-package');
//        $role1->givePermissionTo('verify-account');
//        $role1->givePermissionTo('update-package');
//        $role1->givePermissionTo('update-delivery-destination');
//        $role1->givePermissionTo('append-package-to-delivery');

        $role2 = Role::create(['name' => 'customer-service']);
//        $role2->givePermissionTo('cancel-package');
//        $role2->givePermissionTo('update-package');
//        $role2->givePermissionTo('verify-account');

        $role3 = Role::create(['name' => 'tracing']);
//        $role3->givePermissionTo('update-delivery-destination');
//        $role3->givePermissionTo('append-package-to-delivery');

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
            'name' => 'Ridwan',
            'username' => 'super_ridwan',
            'phone' => '+6287885145866',
            'address' => 'Padang',
            'email' => 'super_ridwan@trawlbens.com',
            'password' => 'Trawlbens456#',
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        $user->assignRole($role1);
        $user = Office::factory()->create([
            'name' => 'Rayendra Trainer',
            'username' => 'super_ray',
            'phone' => '+6287885145866',
            'address' => 'Tebet, Jakarta Selatan',
            'email' => 'super_ray@trawlbens.com',
            'password' => 'Rayen31%xxx',
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        $user->assignRole($role1);


        $user = Office::factory()->create([
            'name' => 'Abu Trainer',
            'username' => 'super_abu',
            'phone' => '+6287885145866',
            'address' => 'Tebet, Jakarta Selatan',
            'email' => 'super_abu@trawlbens.com',
            'password' => 'abu_trawlbens35%',
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        $user->assignRole($role1);

        $user = Office::factory()->create([
            'name' => 'Farhan BP',
            'username' => 'super_farhan',
            'phone' => '+6287885145866',
            'address' => 'Tebet, Jakarta Selatan',
            'email' => 'super_farhan@trawlbens.com',
            'password' => 'farhan_4235%',
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        $user->assignRole($role1);

        $user = Office::factory()->create([
            'name' => 'Ben QC',
            'username' => 'super_ben',
            'phone' => '+6287885145866',
            'address' => 'Tebet, Jakarta Selatan',
            'email' => 'super_ben@trawlbens.com',
            'password' => 'ben_ten35%',
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        $user->assignRole($role1);

        $user = Office::factory()->create([
            'name' => 'Customer Service',
            'username' => 'customer_service',
            'phone' => '+6282246004103',
            'address' => 'Jl. Kebon Jeruk Nomor 45, Jakarta Barat',
            'email' => 'customer_service@trawlbens.com',
            'password' => 'password',
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        $user->assignRole($role2);

        $user = Office::factory()->create([
            'name' => 'Tracing',
            'username' => 'tracing_trawlbens',
            'phone' => '+6284456781234',
            'address' => 'Jl. Rasamala No.35, Jakarta Selatan',
            'email' => 'tracing@trawlbens.com',
            'password' => 'password',
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        $user->assignRole($role3);

        $user = Office::factory()->create([
            'name' => 'Warehouse',
            'username' => 'warehouse_trawlbens',
            'phone' => '+6284456781234',
            'address' => 'Jl. Rasamala No.35, Jakarta Selatan',
            'email' => 'tracing_warehouse@trawlbens.com',
            'password' => 'warehouse_35TBAI',
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        $user->assignRole($role3);

        $user = Office::factory()->create([
            'name' => 'Nia Tracing',
            'username' => 'tracing_nia',
            'phone' => '+6284456781234',
            'address' => 'Jl. Rasamala No.35, Jakarta Selatan',
            'email' => 'tracing_nia@trawlbens.com',
            'password' => 'nia_tbai',
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        $user->assignRole($role3);
    }
}
