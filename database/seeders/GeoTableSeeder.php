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

class GeoTableSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     * @throws \League\Csv\Exception
     */
    public function run()
    {
        $this->command->info('Populating geo data');
        $iso3166 = new ISO3166();
        collect($iso3166->all())->each(function ($item) {
            $c = new Country(Arr::except($item, 'currency'));
            $c->save();

            // populating province.
            $csv = Reader::createFromPath(__DIR__.'/data/iso3166-2.csv');
            $csv->setHeaderOffset(0);

            $statement = (new Statement())
                ->where(fn ($record) => $record['country_code'] === $c->alpha2);

            foreach ($statement->process($csv) as $record) {
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
            }
        });
        $this->command->info('Finished populating geo data.');
    }

    /**
     * Seed regencies by the given country and province.
     *
     * @param \App\Models\Geo\Country  $country
     * @param \App\Models\Geo\Province $province
     *
     * @throws \League\Csv\Exception
     */
    protected function seedRegencies(Country $country, Province $province): void
    {
        $csv = Reader::createFromPath(__DIR__.'/data/geo_regencies_ID.csv');
        $csv->setHeaderOffset(0);

        $statement = (new Statement())
            ->where(fn ($record) => $record['province_iso'] === $province->iso_code);

        foreach ($statement->process($csv) as $record) {
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
        }
    }

    /**
     * Seed district with the given regency.
     *
     * @param \App\Models\Geo\Country  $country
     * @param \App\Models\Geo\Province $province
     * @param \App\Models\Geo\Regency  $regency
     *
     * @throws \League\Csv\Exception
     */
    protected function seedDistricts(Country $country, Province $province, Regency $regency): void
    {
        $csv = Reader::createFromPath(__DIR__.'/data/geo_districts_ID.csv');
        $csv->setHeaderOffset(0);

        $statement = (new Statement())
            ->where(fn ($record) => $record['bsn_code'] === $regency->bsn_code);

        foreach ($statement->process($csv) as $record) {
            $r = new District();
            $r->fill([
                'country_id' => $country->id,
                'province_id' => $province->id,
                'regency_id' => $regency->id,
                'name' => $record['name'],
            ]);
            $r->save();

            $this->seedSubDistricts($country, $province, $regency, $r, $record);
        }
    }

    /**
     * Seed sub district with given district.
     *
     * @param \App\Models\Geo\Country  $country
     * @param \App\Models\Geo\Province $province
     * @param \App\Models\Geo\Regency  $regency
     * @param \App\Models\Geo\District $district
     * @param array                    $original
     *
     * @throws \League\Csv\Exception
     */
    protected function seedSubDistricts(Country $country, Province $province, Regency $regency, District $district, $original = []): void
    {
        $csv = Reader::createFromPath(__DIR__.'/data/geo_subdistricts_ID.csv');
        $csv->setHeaderOffset(0);

        $statement = (new Statement())
            ->where(fn ($record) => $record['district_id'] === $original['identifier']);

        foreach ($statement->process($csv) as $record) {
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
        }
    }
}
