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
            //PartnerTableSeeder::class,
            CustomersTableSeeder::class,
            ServiceTableSeeder::class,
            PriceTableSimpleSeeder::class,
            ProductsTableSeeder::class,
        ]);
    }
}
