<?php

namespace Database\Seeders;

use App\Models\Geo\Country;
use Illuminate\Support\Arr;
use League\ISO3166\ISO3166;
use Illuminate\Database\Seeder;

class GeoCountriesTableSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Populating countries...');
        $iso3166 = new ISO3166();
        collect($iso3166->all())->each(fn ($item) => (new Country(Arr::except($item, 'currency')))->save());
        $this->command->info('Finished populating countries.');
    }
}
