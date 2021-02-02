<?php

namespace Database\Seeders;

use League\Csv\Reader;
use League\Csv\Statement;
use Illuminate\Database\Seeder;
use App\Models\Partners\Partner;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Helper\ProgressBar;

class PartnerTableSeeder extends Seeder
{
    /**
     * @var \Illuminate\Support\Collection
     */
    private Collection $partner;

    /**
     * @var ProgressBar
     */
    private ProgressBar $progressBar;

    /**
     * @param mixed $filePath
     * 
     * @return [type]
     */
    public function loadFiles($filePath)
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
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Load required files...');
        // get data partner
        $this->partner = $this->loadFiles(__DIR__.'/data/partner.csv');

        // showing progressbar
        $this->command->info('Populating partner data');
        $this->progressBar = $this->command->getOutput()->createProgressBar($this->partner->count());

        DB::beginTransaction();
        foreach ($this->partner as $item) {
            $this->progressBar->advance();
            $p = new Partner();
            $p->name = $item['name'];
            $p->address = $item['address'];
            $p->type = $this->modificationType($item['type']);
            $p->save();
        }
        DB::commit();

        // finish seeding
        $this->progressBar->finish();
        $this->command->newLine();
        $this->command->info('Finished populating partner data.');
    }

    protected function modificationType($type)
    {
        switch ($type) {
            case 'WAREHOUSE':
                return 'pool';
            case 'BISNIS':
                return 'business';
            case 'TRANSPORTER':
                return 'transporter';
            case 'SPACE':
                return 'space';
        }
    }
}
