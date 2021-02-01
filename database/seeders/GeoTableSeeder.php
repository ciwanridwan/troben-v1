<?php

namespace Database\Seeders;

use League\Csv\Reader;
use Illuminate\Bus\Batch;
use League\Csv\Statement;
use App\Models\Geo\Country;
use App\Models\Geo\Regency;
use Illuminate\Support\Arr;
use League\ISO3166\ISO3166;
use App\Models\Geo\District;
use App\Models\Geo\Province;
use Illuminate\Database\Seeder;
use App\Jobs\Geo\CreateNewCountry;
use App\Jobs\Geo\CreateNewRegency;
use Illuminate\Support\Collection;
use App\Jobs\Geo\CreateNewDistrict;
use App\Jobs\Geo\CreateNewProvince;
use Illuminate\Support\Facades\Bus;
use App\Jobs\Geo\CreateNewSubDistrict;

class GeoTableSeeder extends Seeder
{
    /**
     * @param $filePath
     * @return \Illuminate\Support\Collection
     * @throws \League\Csv\Exception
     */
    public static function loadFiles($filePath): Collection
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
     * @throws \Throwable
     */
    public function run()
    {
        $this->command->info('Populating geo data');
        $iso3166 = new ISO3166();

        $jobs = collect($iso3166->all())
            ->map(fn ($item) => new CreateNewCountry(Arr::except($item, 'currency')))
            ->filter();

        Bus::batch($jobs->all())
            ->finally(fn (Batch $batch) => self::seedProvinces())
            ->name('geo-seeder-countries')
            ->dispatch();

        $this->command->info('Finished populating geo data.');
    }

    /**
     * Seed Provinces.
     *
     * @throws \League\Csv\Exception|\Throwable
     */
    public static function seedProvinces()
    {
        $iso3166 = self::loadFiles(__DIR__.'/data/iso3166-2.csv');

        $batch = Bus::batch([])
            ->finally(fn (Batch $batch) => self::seedRegencies())
            ->name('geo-seeder-provinces');

        foreach ($iso3166 as $row) {
            $c = Country::query()->where('alpha2', $row['country_code'])->first();

            if ($c instanceof Country) {
                $batch->jobs->push(new CreateNewProvince([
                    'country_id' => $c->id,
                    'name' => $row['subdivision_name'],
                    'iso_code' => $row['code'],
                ]));
            }
        }

        $batch->dispatch();
    }

    /**
     * Seed regencies by the given country and province.
     *
     * @throws \League\Csv\Exception
     * @throws \Throwable
     */
    public static function seedRegencies(): void
    {
        $regencies = self::loadFiles(__DIR__.'/data/geo_regencies_ID.csv');

        $batch = Bus::batch([])
            ->finally(fn (Batch $batch) => self::seedDistricts())
            ->name('geo-seeder-regencies');

        foreach ($regencies as $row) {
            $p = Province::query()->where('iso_code', $row['province_iso'])->first();
            if ($p instanceof Province) {
                $batch->jobs->push(
                    new CreateNewRegency([
                        'country_id' => $p->country_id,
                        'province_id' => $p->id,
                        'name' => $row['regency'],
                        'capital' => $row['capital'],
                        'bsn_code' => $row['bsn_code'],
                    ])
                );
            }
        }

        $batch->dispatch();
    }

    /**
     * Seed district with the given regency.
     *
     * @throws \League\Csv\Exception|\Throwable
     */
    public static function seedDistricts(): void
    {
        $districts = self::loadFiles(__DIR__.'/data/geo_districts_ID.csv');

        $batch = Bus::batch([])
            ->finally(fn (Batch $batch) => self::seedSubDistricts())
            ->name('geo-seeder-districts');

        foreach ($districts as $row) {
            $r = Regency::query()->where('bsn_code', $row['bsn_code'])->first();
            if ($r instanceof Regency) {
                $batch->jobs->push(new CreateNewDistrict([
                    'country_id' => $r->country_id,
                    'province_id' => $r->province_id,
                    'regency_id' => $r->id,
                    'name' => $row['name'],
                ]));
            }
        }

        $batch->dispatch();
    }

    /**
     * Seed sub districts with the given districts.
     *
     * @throws \League\Csv\Exception|\Throwable
     */
    public static function seedSubDistricts(): void
    {
        $districts = self::loadFiles(__DIR__.'/data/geo_districts_ID.csv');
        $subDistricts = self::loadFiles(__DIR__.'/data/geo_sub_districts_ID.csv')->groupBy('district_id');

        $batch = Bus::batch([])
            ->name('geo-seeder-sub-districts');

        foreach ($districts as $district) {
            $d = District::query()
                ->whereHas('regency', fn ($q) => $q->where('bsn_code', $district['bsn_code']))
                ->where('name', $district['name'])
                ->first();

            if ($d instanceof District) {
                $subDistricts
                    ->get($district['identifier'])
                    ->each(fn ($row) => $batch->jobs->push(
                        new CreateNewSubDistrict([
                            'country_id' => $d->country_id,
                            'province_id' => $d->province_id,
                            'regency_id' => $d->regency_id,
                            'district_id' => $d->id,
                            'name' => $row['name'],
                            'zip_code' => $row['zip_code'],
                        ])
                    ));
            }
        }

        $batch->dispatch();
    }
}
