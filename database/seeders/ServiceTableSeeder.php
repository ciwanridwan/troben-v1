<?php

namespace Database\Seeders;

use League\Csv\Reader;
use App\Models\Service;
use League\Csv\Statement;
use Illuminate\Database\Seeder;

class ServiceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $csv = Reader::createFromPath(__DIR__.'/data/trawlpack_services.csv');
        $csv->setHeaderOffset(0);

        foreach ((new Statement())->process($csv) as $service) {
            if ($service['code'] != Service::TRAWLPACK_STANDARD) {
                $service['is_active'] = false;
            }
            (new Service())->fill($service)->save();
        }
    }
}
