<?php

namespace Database\Seeders;

use App\Models\Price;
use League\Csv\Reader;
use App\Models\Service;
use League\Csv\Statement;
use App\Models\Geo\Regency;
use Illuminate\Support\Arr;
use App\Models\Geo\SubDistrict;
use Illuminate\Database\Seeder;

class PriceTableSimpleSeeder extends Seeder
{
    protected $serviceMapper = [
        'TPI' => Service::TRAWLPACK_INSTANT,
        'TPX' => Service::TRAWLPACK_EXPRESS,
        'TPD' => Service::TRAWLPACK_SAMEDAY,
        'TPS' => Service::TRAWLPACK_STANDARD,
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     * @throws \League\Csv\Exception
     */
    public function run(): void
    {
        $csv = Reader::createFromPath(__DIR__.'/data/price_list.csv');
        $csv->setHeaderOffset(0);

        $regency = null;
        foreach ((new Statement())->process($csv) as $value) {
            $value['service_code'] = $this->serviceMapper[$value['service_code']];

            // get regency by bsn_code
            /** @var \App\Models\Geo\Regency|null $regency */
            if (is_null($regency) || $regency->bsn_code != $value['bsn_code']) {
                $regency = Regency::query()->firstOrCreate(['bsn_code' => $value['bsn_code']]);
            }

            // seed price
            Price::create(array_merge(
                Arr::except($value, 'bsn_code'),
                [
                    'origin_province_id' => $regency->province_id,
                    'origin_regency_id' => $regency->getKey(),
                    'destination_id' => SubDistrict::query()->where('zip_code', $value['zip_code'])->first()->getKey(),
                ]
            ));
        }
    }
}
