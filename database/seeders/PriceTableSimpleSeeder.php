<?php

namespace Database\Seeders;

use App\Models\Price;
use League\Csv\Reader;
use App\Models\Service;
use League\Csv\Statement;
use App\Models\Geo\Regency;
use Illuminate\Database\Seeder;

class PriceTableSimpleSeeder extends Seeder
{
    protected $serviceMapper = [
        'PGD' => [
            'code' => 'pgd',
            'name' => 'TrawlPack Ground Delivery',
            'description' => 'Forwarding ...',
        ],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $csv = Reader::createFromPath(__DIR__.'/data/price_list.csv');
        $csv->setHeaderOffset(0);

        $service = null;
        $regency = null;
        foreach ((new Statement())->process($csv) as $value) {
            // seed services
            if (is_null($service) || $service->code != $this->serviceMapper[$value['service_code']]['code']) {
                $service = (new Service())->fill([
                    'code' => $this->serviceMapper[$value['service_code']]['code'],
                    'name' => $this->serviceMapper[$value['service_code']]['name'],
                    'description' => $this->serviceMapper[$value['service_code']]['description'],
                ]);
                $service->save();
            }
            $value['service_code'] = $service->getKey();

            // get regency by bsn_code
            if (is_null($regency) || $regency->bsn_code != $value['bsn_code']) {
                $regency = (new Regency(['bsn_code' => $value['bsn_code']]))->first();
            }

            // seed price
            unset($value['bsn_code']);
            Price::create(array_merge(
                array_filter($value),
                ['origin_regency_id' => $regency->getKey()]
            ));
        }
    }
}
