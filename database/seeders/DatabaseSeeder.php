<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            UsersTableSeeder::class,
            GeoTableSimpleSeeder::class,
            // GeoTableImport::class,
            //PartnerTableSeeder::class,
            CustomersTableSeeder::class,
            ServiceTableSeeder::class,
            PriceTableSimpleSeeder::class,
            // PriceTableSimpleSeeder::class,
            ProductsTableSeeder::class,
        ]);
    }
}
