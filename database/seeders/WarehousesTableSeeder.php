<?php

namespace Database\Seeders;

use App\Models\Partners\Partner;
use App\Models\Partners\Warehouse;
use Illuminate\Database\Seeder;

class WarehousesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->prepareDependency();
        /** @var Partner $partners */
        $partners = Partner::query()->where('type', Partner::TYPE_POOL)->whereDoesntHave('warehouses')->get();

        $this->command->info("Start Create Warehouse For Partners");
        $partners->each(fn (Partner $partner) => $partner->warehouses()->create(Warehouse::factory()->makeOne()->toArray()))->each(fn (Partner $partner) => $this->command->warn("=> Create Warehouse For Partner " . $partner->code));

        //
    }
    public function prepareDependency()
    {
        if (Partner::query()->where('type', Partner::TYPE_POOL)->count() === 0) {
            $this->call(UsersTableSeeder::class);
        }
    }
}
