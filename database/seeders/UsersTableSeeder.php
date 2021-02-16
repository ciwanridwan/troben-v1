<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Factories\Sequence;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // make sure at least one record contain this data.
        User::factory()
            ->makeOne([
                'username' => 'admin',
                'phone' => '+625555555555',
                'email' => 'user@trawlbens.co.id',
                'verified_at' => Carbon::now(),
            ])->save();

        // make the rest of the data.
        User::factory()
            ->count(9)
            ->state(new Sequence(
                ['verified_at' => null],
                ['verified_at' => Carbon::now()]
            ))
            ->create();
    }
}
