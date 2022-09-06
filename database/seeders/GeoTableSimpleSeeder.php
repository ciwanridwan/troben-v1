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
use libphonenumber\PhoneNumberUtil;

class GeoTableSimpleSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     * @throws \League\Csv\Exception
     */
    public function run()
    {
        // seed country
        $idn = new Country();
        $idn->fill(array_merge(
            Arr::except((new ISO3166())->alpha2('ID'), 'currency'),
            ['phone_prefix' => PhoneNumberUtil::getInstance()->getCountryCodeForRegion('ID')]
        ));
        $idn->save();

        $csv = Reader::createFromPath(__DIR__.'/data/geo_simple_data.csv');
        $csv->setHeaderOffset(0);

        // set pointer.
        $province = null;
        $regency = null;
        $district = null;
        foreach ((new Statement())->process($csv) as $record) {
            // resolve province.
            /** @var \App\Models\Geo\Province|null $province */
            if (is_null($province) || $province->iso_code !== $record['province_code']) {
                $province = new Province();
                $province->fill([
                    'country_id' => $idn->id,
                    'name' => $record['province_name'],
                    'iso_code' => $record['province_code'],
                ]);
                $province->save();
            }

            // resolve regency
            /** @var \App\Models\Geo\Regency|null $regency */
            if (is_null($regency) || $regency->bsn_code !== $record['regency_code']) {
                $regency = new Regency();
                $regency->fill([
                    'country_id' => $idn->id,
                    'province_id' => $province->id,
                    'name' => $record['regency_name'],
                    'bsn_code' => $record['regency_code'],
                ]);
                $regency->save();
            }

            // resolve district
            /** @var \App\Models\Geo\District|null $district */
            if (is_null($district) || $district->name !== $record['district']) {
                $district = new District();
                $district->fill([
                    'country_id' => $idn->id,
                    'province_id' => $province->id,
                    'regency_id' => $regency->id,
                    'name' => $record['district'],
                ]);
                $district->save();
            }

            $subDistrict = new SubDistrict();
            $subDistrict->fill([
                'country_id' => $idn->id,
                'province_id' => $province->id,
                'regency_id' => $regency->id,
                'district_id' => $district->id,
                'name' => $record['sub_district'],
                'zip_code' => $record['zip_code'],
            ]);
            $subDistrict->save();
        }
    }
}
