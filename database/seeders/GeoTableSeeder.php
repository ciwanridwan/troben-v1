<?php

namespace Database\Seeders;

use League\Csv\Reader;
use League\Csv\Statement;
use App\Models\Geo\Country;
use App\Models\Geo\Regency;
use Illuminate\Support\Arr;
use League\ISO3166\ISO3166;
use App\Models\Geo\District;
use App\Models\Geo\Province;
use App\Models\Geo\SubDistrict;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use libphonenumber\PhoneNumberUtil;
use Symfony\Component\Console\Helper\ProgressBar;

class GeoTableSeeder extends Seeder
{
    /**
     * @var \Illuminate\Support\Collection
     */
    private Collection $regencies;

    /**
     * @var \Illuminate\Support\Collection
     */
    private Collection $districts;

    /**
     * @var \Illuminate\Support\Collection
     */
    private Collection $subDistricts;

    private ProgressBar $progressBar;

    /**
     * @param $filePath
     * @return \Illuminate\Support\Collection
     * @throws \League\Csv\Exception
     */
    public function loadFiles($filePath): Collection
    {
        $collection = new Collection();

        $csv = Reader::createFromPath($filePath);
        $csv->setHeaderOffset(0);

        foreach ((new Statement())->process($csv) as $item) {
            $collection->add($item);
        }

        return $collection;
    }

    /**
     * Seed the application's database.
     *
     * @return void
     * @throws \League\Csv\Exception
     */
    public function run()
    {
        // truncate existing tables.
        DB::table('geo_sub_districts')->truncate();
        DB::table('geo_districts')->truncate();
        DB::table('geo_regencies')->truncate();
        DB::table('geo_provinces')->truncate();
        DB::table('geo_countries')->truncate();

        $this->command->info('Load required files...');
        $iso3166data = $this->loadFiles(__DIR__.'/data/iso3166-2.csv')->groupBy('country_code');
        $this->regencies = $this->loadFiles(__DIR__.'/data/geo_regencies_ID.csv')->groupBy('province_iso');
        $this->districts = $this->loadFiles(__DIR__.'/data/geo_districts_ID.csv')->groupBy('bsn_code');
        $subDistricts = $this->loadFiles(__DIR__.'/data/geo_sub_districts_ID.csv');

        $totalSubDistrict = $subDistricts->count();
        $this->subDistricts = $subDistricts->groupBy('district_id');

        $this->command->info('Populating geo data');
        $iso3166 = new ISO3166();

        $this->progressBar = $this->command->getOutput()->createProgressBar($totalSubDistrict);

        foreach ($iso3166->all() as $item) {
            $c = new Country(Arr::except($item, 'currency'));
            $c->phone_prefix = PhoneNumberUtil::getInstance()->getCountryCodeForRegion($item['alpha2']);
            $c->save();

            if (empty($iso3166data->get($c->alpha2))) {
                // $this->command->warn('[iso3166] '.$c->alpha2.' not found!');
                continue;
            }

            // populating province.
            $iso3166data->get($c->alpha2)->each(function ($record) use ($c) {
                $p = new Province();
                $p->fill([
                    'country_id' => $c->id,
                    'name' => $record['subdivision_name'],
                    'iso_code' => $record['code'],
                ]);
                $p->save();

                // if in indonesia, let's go down further
                if ($c->alpha2 === 'ID') {
                    $this->seedRegencies($c, $p);
                }
            });
        }

        $this->progressBar->finish();
        $this->command->newLine();
        $this->command->info('Finished populating geo data.');
    }

    /**
     * Seed regencies by the given country and province.
     *
     * @param \App\Models\Geo\Country $country
     * @param \App\Models\Geo\Province $province
     *
     */
    protected function seedRegencies(Country $country, Province $province): void
    {
        $this->regencies->get($province->iso_code)->each(function ($record) use ($country, $province) {
            $r = new Regency();
            $r->fill([
                'country_id' => $country->id,
                'province_id' => $province->id,
                'name' => $record['regency'],
                'capital' => $record['capital'],
                'bsn_code' => $record['bsn_code'],
            ]);

            $r->save();

            $this->seedDistricts($country, $province, $r);
        });
    }

    /**
     * Seed district with the given regency.
     *
     * @param \App\Models\Geo\Country $country
     * @param \App\Models\Geo\Province $province
     * @param \App\Models\Geo\Regency $regency
     *
     */
    protected function seedDistricts(Country $country, Province $province, Regency $regency): void
    {
        $this->districts->get($regency->bsn_code)->each(function ($record) use ($country, $province, $regency) {
            $r = new District();
            $r->fill([
                'country_id' => $country->id,
                'province_id' => $province->id,
                'regency_id' => $regency->id,
                'name' => $record['name'],
            ]);
            $r->save();

            $this->seedSubDistricts($country, $province, $regency, $r, $record);
        });
    }

    /**
     * Seed sub district with given district.
     *
     * @param \App\Models\Geo\Country $country
     * @param \App\Models\Geo\Province $province
     * @param \App\Models\Geo\Regency $regency
     * @param \App\Models\Geo\District $district
     * @param array $original
     *
     */
    protected function seedSubDistricts(Country $country, Province $province, Regency $regency, District $district, $original = []): void
    {
        $this->subDistricts->get($original['identifier'])->each(function ($record) use ($country, $province, $regency, $district) {
            $this->progressBar->advance();

            $r = new SubDistrict();
            $r->fill([
                'country_id' => $country->id,
                'province_id' => $province->id,
                'regency_id' => $regency->id,
                'district_id' => $district->id,
                'name' => $record['name'],
                'zip_code' => $record['zip_code'],
            ]);
            $r->save();
        });
    }
}
