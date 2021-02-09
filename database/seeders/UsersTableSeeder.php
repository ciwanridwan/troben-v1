<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

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
