<?php

namespace Database\Seeders;

use Database\Seeders\Packages\AssignedPackagesSeeder;
use Database\Seeders\Packages\PackagesTableSeeder;
use Illuminate\Database\Seeder;

class StagingDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            UsersTableSeeder::class,
            GeoTableSimpleSeeder::class,
            // GeoTableImport::class,
            // PartnerTableImport::class,
            CustomersTableSeeder::class,
            ServiceTableSeeder::class,
            HandlingSeeder::class,
            PriceTableSimpleSeeder::class,
            // PriceTableSimpleSeeder::class,
            ProductsTableSeeder::class,
            PackagesTableSeeder::class,
            AssignedPackagesSeeder::class,
        ]);
    }
}
