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
    const TIMEOUT = 60;

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

        $batch = Bus::batch([])
            ->finally(fn (Batch $batch) => self::seedProvinces())
            ->name('geo-seeder-countries')
            ->dispatch();

        $batch->add($jobs->all());

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
            ->name('geo-seeder-provinces')
            ->dispatch();

        $iso3166->each(function ($row) use ($batch) {
            $ticker = 0;
            while ($ticker < self::TIMEOUT) {
                $c = Country::query()->where('alpha2', $row['country_code'])->first();
                if ($c instanceof Country) {
                    $batch->add([
                        new CreateNewProvince([
                            'country_id' => $c->id,
                            'name' => $row['subdivision_name'],
                            'iso_code' => $row['code'],
                        ]),
                    ]);
                    break;
                }
                $ticker++;
                sleep(1);
            }
        });
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
            ->name('geo-seeder-regencies')
            ->dispatch();

        $regencies
            ->each(function ($row) use ($batch) {
                $ticker = 0;
                while ($ticker < self::TIMEOUT) {
                    $p = Province::query()->where('iso_code', $row['province_iso'])->first();

                    if ($p instanceof Province) {
                        $batch->add([
                            new CreateNewRegency([
                                'country_id' => $p->country_id,
                                'province_id' => $p->id,
                                'name' => $row['regency'],
                                'capital' => $row['capital'],
                                'bsn_code' => $row['bsn_code'],
                            ]),
                        ]);
                        break;
                    }
                    $ticker++;
                    sleep(1);
                }
            });
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
            ->name('geo-seeder-districts')
            ->dispatch();

        $districts->each(function ($row) use ($batch) {
            $ticker = 0;
            while ($ticker < self::TIMEOUT) {
                $r = Regency::query()->where('bsn_code', $row['bsn_code'])->first();

                if ($r instanceof Regency) {
                    $batch->add([
                        new CreateNewDistrict([
                            'country_id' => $r->country_id,
                            'province_id' => $r->province_id,
                            'regency_id' => $r->id,
                            'name' => $row['name'],
                        ]),
                    ]);
                    break;
                }
                $ticker++;
                sleep(1);
            }
        });
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

        $subDistrictBatch = Bus::batch([])
            ->name('geo-seeder-sub-districts')
            ->dispatch();

        foreach ($districts as $rowDistrict) {
            $ticker = 0;
            while ($ticker < self::TIMEOUT) {
                $d = District::query()->where('name', $rowDistrict['name'])->whereHas('regency', fn ($q) => $q->where('bsn_code', $rowDistrict['bsn_code']))->first();
                if ($d instanceof District) {
                    $subDistricts
                        ->get($rowDistrict['identifier'])
                        ->each(fn ($row) => $subDistrictBatch->add([
                            new CreateNewSubDistrict([
                                'country_id' => $d->country_id,
                                'province_id' => $d->province_id,
                                'regency_id' => $d->regency_id,
                                'district_id' => $d->id,
                                'name' => $row['name'],
                                'zip_code' => $row['zip_code'],
                            ]),
                        ]));
                    break;
                }
                sleep(1);
            }
        }
    }
}
