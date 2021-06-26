<?php

namespace Database\Seeders;

use App\Models\Geo\Province;
use App\Models\Price;
use League\Csv\Reader;
use App\Models\Service;
use League\Csv\Statement;
use App\Models\Geo\Regency;
use Illuminate\Support\Arr;
use App\Models\Geo\SubDistrict;
use Illuminate\Database\Seeder;
use Symfony\Component\Console\Helper\ProgressBar;

class PriceTableSeeder extends Seeder
{

    private ProgressBar $progressBar;

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
        $csv = Reader::createFromPath(__DIR__ . '/data/price_list_upload.csv');
        $csv->setHeaderOffset(0);

        $this->command->info('Populating Pricing data');
        $totalPrices = $csv->count();
        $this->progressBar = $this->command->getOutput()->createProgressBar($totalPrices);


        $sub_district = null;
        /** @var Province $province */
        $province = Province::query()->where('name', 'DKI Jakarta')->first();

        foreach ((new Statement())->process($csv) as $value) {
            try {
                $value['service_code'] = $this->serviceMapper[strtoupper($value['service_code'])];
            } catch (\Throwable $th) {
                continue;
            }
            $this->progressBar->advance();

            // get sub_district by name
            /** @var \App\Models\Geo\SubDistrict|null $sub_district */
            $sub_district = SubDistrict::query()->where('name', $value['destination_name'])->where('zip_code', $value['zip_code'])->first();

            $province->regencies->each(function (Regency $regency) use ($sub_district, $value) {
                // seed price
                Price::create([
                    'origin_province_id' => $regency->province_id,
                    'origin_regency_id' => $regency->getKey(),
                    'destination_id' => $sub_district->getKey(),
                    'zip_code' => $sub_district->zip_code,
                    'service_code' => $value['service_code']
                ]);
            });
        }
        $this->progressBar->finish();
        $this->command->newLine();
        $this->command->info('Finished populating Pricing data.');
    }
}
