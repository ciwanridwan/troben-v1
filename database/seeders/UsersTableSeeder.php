<?php

namespace Database\Seeders;

use App\Models\Partners\Partner;
use App\Models\Partners\Pivot\UserablePivot;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Eloquent\Model;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Partner::factory(1)->create();
        $p = Partner::first();

        // make sure at least one record contain this data.
        User::factory(1)->create([
            'username' => 'admin',
            'phone' => '+625555555555',
            'email' => 'user@trawlbens.co.id',
            'verified_at' => Carbon::now(),
        ]);
        $u = User::first();

        $pivot = new UserablePivot();
        $pivot->fill([
            'user_id' => $u->id,
            'userable_type' => Model::getActualClassNameForMorph(get_class($p)),
            'userable_id' => $p->getKey(),
            'role' => 'owner',
        ]);
        $pivot->save();

        // make the rest of the data.
        // User::factory()
        //     ->count(9)
        //     ->state(new Sequence(
        //         ['verified_at' => null],
        //         ['verified_at' => Carbon::now()]
        //     ))
        //     ->create();
    }
}
