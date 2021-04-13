<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\Packages\PackagesTableSeeder;
use Database\Seeders\Packages\CashierInChargeSeeder;
use Database\Seeders\Packages\AssignedPackagesSeeder;
use Database\Seeders\Packages\CustomerInChargeSeeder;
use Database\Seeders\Packages\WarehouseInChargeSeeder;
use Database\Seeders\Packages\PostPayment\PackedSeeder;
use Database\Seeders\Packages\PostPayment\PostPaymentSeeder;

class StagingDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        UsersTableSeeder::$COUNT = 3;

        $this->command->getOutput()->title('Common seeder');
        $this->call([
            UsersTableSeeder::class,
            GeoTableSimpleSeeder::class,
            CustomersTableSeeder::class,
            ServiceTableSeeder::class,
            PriceTableSimpleSeeder::class,
            ProductsTableSeeder::class,
        ]);

        PackagesTableSeeder::$CUSTOMER_PACKAGES = 4;
        $this->command->getOutput()->title('Pickup flow seeder');
        $this->call([
            PackagesTableSeeder::class,
            TransportersTableSeeder::class,
            AssignedPackagesSeeder::class,
            WarehouseInChargeSeeder::class,
            CashierInChargeSeeder::class,
            CustomerInChargeSeeder::class,
        ]);

        PostPaymentSeeder::$CUSTOMER_PACKAGES = 1;
        $this->command->getOutput()->title('Post payment seeder');
        $this->call([
            PostPaymentSeeder::class,
            PackedSeeder::class,
        ]);
    }
}
