<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BulkTransitPrice extends Command
{
    // Declare some constanta
    private const TYPE_MTAK_1 = '1';
    private const TYPE_MTAK_2 = '2';
    private const TYPE_MTAK_3 = '3';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'price:transit
                            {type : MTAK Type 1, 2 Or 3 (possible values: 1,2,3)}
                            {--F|file= : File path location, the type must be csv}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bulk data transit price (insert or update)';

    private string $type;

    private string $file_path;

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
        $header = [
            'id', 'prov', 'kota_kab', 'kec', 'kel', 'zipcode', 'vendor_1', 'vendor_2', 'vendor_3',
            'tier_1', 'tier_2', 'tier_3', 'tier_4', 'tier_5', 'tier_6', 'tier_7', 'tier_8', "ket"
        ];

        $this->type = $this->argument('type');
        if (! in_array($this->type, self::getAvailableType())) {
            $this->error('Wrong type argument');
            return;
        }

        $this->file_path = $this->option('file');
        if (! $this->file_path) {
            $this->error('Option file is required');
            return;
        }

        $f = \Illuminate\Support\Facades\File::exists($this->file_path);
        if (!$f) {
            $this->info('file not found');
            return;
        }

        $rows = [];
        $data = $this->csv_to_array($this->file_path, $header);
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

            if ($this->type === self::TYPE_MTAK_1) {
                $rows[] = $this->firstType($r, $d);
            } elseif ($this->type === self::TYPE_MTAK_2) {
                $rows[] = $this->secondType($r, $d);
            } elseif ($this->type === self::TYPE_MTAK_3) {
                $rows[] = $this->thirdType($r, $d);
            }


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

        $this->info('Update transit price finished.');
    }

    private function csv_to_array($filename = '', $header)
    {
        $delimiter = ',';
        if (!file_exists($filename) || !is_readable($filename))
            return FALSE;

        $data = array();
        if (($handle = fopen($filename, 'r')) !== FALSE) {
            while (($row = fgetcsv($handle, 0, $delimiter)) !== FALSE) {
                $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }

        return $data;
    }

    /**
     * @return string[]
     */
    private static function getAvailableType(): array
    {
        return [
            self::TYPE_MTAK_1,
            self::TYPE_MTAK_2,
            self::TYPE_MTAK_3
        ];
    }

    /**
     * @return array
     * This rows for inserting to tables
     */
    private function firstType($r, $d): array
    {
        return [
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
    }

    /**
     * @return array
     * This rows for inserting to tables
     */
    private function secondType($r, $d): array
    {
        return [
            'origin_regency_id' => 1,
            'destination_regency_id' => $r->regency_id,
            'destination_district_id' => $r->id,
            'type' => 2,
            'vendor' => $d['vendor_2'],
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
    }

    /**
     * @return array
     * This rows for inserting to tables
     */
    private function thirdType($r, $d): array
    {
        return [
            'origin_regency_id' => 1,
            'destination_regency_id' => $r->regency_id,
            'destination_district_id' => $r->id,
            'type' => 3,
            'vendor' => $d['vendor_3'],
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
    }
}
