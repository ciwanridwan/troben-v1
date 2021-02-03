<?php

namespace Database\Seeders;

use App\Models\User;
use League\Csv\Reader;
use App\Models\Userable;
use League\Csv\Statement;
use Illuminate\Database\Seeder;
use App\Models\Partners\Partner;
use App\Models\Partners\Warehouse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\Partners\Transporter;
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
     * @param $filePath
     * @return \Illuminate\Support\Collection
     * @throws \League\Csv\Exception
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
     * Seed the application's database.
     *
     * @return void
     * @throws \League\Csv\Exception
     */
    public function run()
    {
        $this->command->info('Load required files...');
        $this->partner = $this->loadFiles(__DIR__.'/data/partner.csv');

        $this->command->info('Populating partner data');
        $this->progressBar = $this->command->getOutput()->createProgressBar($this->partner->count());

        foreach ($this->partner as $item) {
            DB::beginTransaction();
            $this->progressBar->advance();
            $p = new Partner();
            $p->name = $item['name'];
            $p->address = $item['address'];
            $p->type = $this->modificationType($item['type']);
            $p->save();

            $u = new User();
            $u->name = $item['name'];
            $u->email = $item['email'];
            $u->password = 'Trawlbens'.$item['phonenumber']; //test data
            $u->save();

            $uable = new Userable();
            $uable->user_id = $u->id;
            $uable->partnerable()
                ->associate($p)
                ->save();

            $this->validatePartner($p, $item);
            DB::commit();
        }

        $this->progressBar->finish();
        $this->command->newLine();
        $this->command->info('Finished populating partner data.');
    }

    /**
     * Generate new type.
     * 
     * @param String $type
     * 
     * @return string
     */
    protected function modificationType(String $type) : string
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

    /**
     * Validate partner type for creating warehouse and transporter.
     * 
     * @param Partner $partner
     * @param array $original
     * 
     * @return void
     */
    protected function validatePartner(Partner $partner, $original = []) : void
    {
        if (in_array($partner->type, ['pool','space','business'])) {
            $w = new Warehouse();
            $w->partner_id = $partner->id;
            $w->code = $original['code']; //data test
            $w->name = $original['name']; //data test
            $w->is_pool = $partner->type == 'pool' ? true : false;
            $w->is_counter = in_array($partner->type, ['business','space']) ? true : false;
            $w->save();
        }

        if (in_array($partner->type, ['business','transporter'])) {
            $t = new Transporter();
            $t->partner_id = $partner->id;
            $t->name = $original['code']; //data test
            $t->registration_number = 'stnk'; //data test
            $t->type = 'bike'; //data test
            $t->save();
        }
    }
}
