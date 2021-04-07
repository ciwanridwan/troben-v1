<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\Packages\PackagesTableSeeder;
use Database\Seeders\Packages\CashierInChargeSeeder;
use Database\Seeders\Packages\AssignedPackagesSeeder;
use Database\Seeders\Packages\CustomerInChargeSeeder;
use Database\Seeders\Packages\WarehouseInChargeSeeder;

class StagingDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PackagesTableSeeder::$CUSTOMER_PACKAGES = 4;

        $this->call([
            UsersTableSeeder::class,
            GeoTableSimpleSeeder::class,
            CustomersTableSeeder::class,
            ServiceTableSeeder::class,
            PriceTableSimpleSeeder::class,
            ProductsTableSeeder::class,
            PackagesTableSeeder::class,
            TransportersTableSeeder::class,
            AssignedPackagesSeeder::class,
            WarehouseInChargeSeeder::class,
            CashierInChargeSeeder::class,
            CustomerInChargeSeeder::class,
        ]);
    }
}
