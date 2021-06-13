<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use App\Models\Customers\Customer;

class CustomersTableSeeder extends Seeder
{
    public static int $CUSTOMER_CREATED = 9;
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // make sure at least one record contain this data.
        Customer::factory()
            ->makeOne([
                'phone' => '+625555555555',
                'email' => 'customer@trawlbens.co.id',
                'email_verified_at' => Carbon::now(),
                'phone_verified_at' => Carbon::now(),
            ])->save();

        // make the rest of the data.
        Customer::factory()
            ->count(self::$CUSTOMER_CREATED)
            ->state([
                'email_verified_at' => Carbon::now(),
                'phone_verified_at' => Carbon::now(),
            ])
            ->create();

        $this->command->table(['name', 'phone', 'email'], Customer::query()->get()->map(fn (Customer $customer) => [
            $customer->name,
            $customer->phone,
            $customer->email,
        ]));
    }
}
