<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BulkTransitPrice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'price:transit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bulk data transit price (insert or update)';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $file = database_path('csv/mtak_1_bandung.csv');
        $header = [
            'id', 'prov', 'kota_kab', 'kec', 'kel', 'zipcode', 'vendor_1', 'vendor_2', 'vendor_3',
            'tier_1', 'tier_2', 'tier_3', 'tier_4', 'tier_5', 'tier_6', 'tier_7', 'tier_8', "ket"
        ];
        // dd($header);
        $f = \Illuminate\Support\Facades\File::exists($file);
        if (!$f) {
            $this->info('file not found');
            return;
        }

        $rows = [];
        $data = $this->csv_to_array($file, $header);
        foreach ($data as $k => $d) {
            if ($k == 0) continue;

            if (($k % 100) == 0) $this->info($k);

            $q = "SELECT * FROM geo_districts WHERE name ILIKE '%s' LIMIT 1";
            $q = sprintf($q, pg_escape_string($d['kec']));
            $z = DB::select($q);

            if (count($z) == 0) {
                $m = sprintf('not found: %s', $d['kec']);
                $this->error($m);
                break; // stop!
            }

            $r = $z[0];

            $rows[] = [
                'origin_regency_id' => 1,
                'destination_regency_id' => $r->regency_id,
                'destination_district_id' => $r->id,
                'type' => 1,
                'vendor' => $d['vendor_1'],
                'tier_1' => $d['tier_1'],
                'tier_2' => $d['tier_2'],
                'tier_3' => $d['tier_3'],
                'tier_4' => $d['tier_4'],
                'tier_5' => $d['tier_5'],
                'tier_6' => $d['tier_6'],
                'tier_7' => $d['tier_7'],
                'tier_8' => $d['tier_8'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];

            if (count($rows) == 100) {
                try {
                    DB::table('partner_transit_prices')->insert($rows);
                } catch (\Exception $e) {
                    dd($e);
                }
                $rows = [];
            }
        }

        if (count($rows)) {
            DB::table('partner_transit_prices')->insert($rows);
            $this->info(count($rows));
        }
    }

    private function csv_to_array($filename='', $header)
    {
        $delimiter=',';
        if(!file_exists($filename) || !is_readable($filename))
            return FALSE;

        $data = array();
        if (($handle = fopen($filename, 'r')) !== FALSE)
        {
            while (($row = fgetcsv($handle, 0, $delimiter)) !== FALSE)
            {
                $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }

        return $data;
    }
}
