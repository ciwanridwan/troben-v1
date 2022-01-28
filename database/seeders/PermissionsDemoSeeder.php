<?php

namespace Database\Seeders;

use App\Models\Offices\Office;
use App\Models\Offices\Role;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PermissionsDemoSeeder extends Seeder
{
    /**
     * Create the initial roles and permissions.
     *
     * @return void
     */
    public function run()
    {
        // create roles and assign existing permissions
        $role1 = Role::factory()->create(['name' => 'super-admin']);
        $role2 = Role::factory()->create(['name' => 'customer-service']);
        $role3 = Role::factory()->create(['name' => 'tracing']);

        $user = Office::factory()->create([
            'name' => 'Super Admin',
            'username' => 'super_admin',
            'phone' => '+6287885145866',
            'address' => 'Depok 2 Tengah',
            'email' => 'super@trawlbens.com',
            'password' => 'password',
            'role_id' => $role1->id,
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        $user = Office::factory()->create([
            'name' => 'Ridwan',
            'username' => 'super_ridwan',
            'phone' => '+6287885145333',
            'address' => 'Padang',
            'email' => 'super_ridwan@trawlbens.com',
            'password' => 'Trawlbens456#',
            'role_id' => $role1->id,
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        $user = Office::factory()->create([
            'name' => 'Rayendra Trainer',
            'username' => 'super_ray',
            'phone' => '+6287884444866',
            'address' => 'Tebet, Jakarta Selatan',
            'email' => 'super_ray@trawlbens.com',
            'password' => 'Rayen31%xxx',
            'role_id' => $role1->id,
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $user = Office::factory()->create([
            'name' => 'Abu Trainer',
            'username' => 'super_abu',
            'phone' => '+6287885145111',
            'address' => 'Tebet, Jakarta Selatan',
            'email' => 'super_abu@trawlbens.com',
            'password' => 'abu_trawlbens35%',
            'role_id' => $role1->id,
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $user = Office::factory()->create([
            'name' => 'Ben QC',
            'username' => 'super_ben',
            'phone' => '+6287885146789',
            'address' => 'Tebet, Jakarta Selatan',
            'email' => 'super_ben@trawlbens.com',
            'password' => 'ben_ten35%',
            'role_id' => $role1->id,
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $user = Office::factory()->create([
            'name' => 'Customer Service',
            'username' => 'customer_service',
            'phone' => '+6282246009011',
            'address' => 'Jl. Kebon Jeruk Nomor 45, Jakarta Barat',
            'email' => 'customer_service@trawlbens.com',
            'password' => 'password',
            'role_id' => $role2->id,
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $user = Office::factory()->create([
            'name' => 'Tracing',
            'username' => 'tracing_trawlbens',
            'phone' => '+6284456784444',
            'address' => 'Jl. Rasamala No.35, Jakarta Selatan',
            'email' => 'tracing@trawlbens.com',
            'password' => 'password',
            'role_id' => $role3->id,
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $user = Office::factory()->create([
            'name' => 'Warehouse',
            'username' => 'warehouse_trawlbens',
            'phone' => '+6284422781234',
            'address' => 'Jl. Rasamala No.35, Jakarta Selatan',
            'email' => 'tracing_warehouse@trawlbens.com',
            'password' => 'warehouse_35TBAI',
            'role_id' => $role3->id,
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
