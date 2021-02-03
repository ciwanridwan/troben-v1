<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customers\Customer;

class CustomersTableSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Customer::factory(10)->create();
    }
}
