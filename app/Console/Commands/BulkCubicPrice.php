<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BulkCubicPrice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'price:cubic
                            {--F|file= : File path location, the type must be csv}
                            {--R|regency= : exists id from geo regencies}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bulk data cubic price (insert or update)';

    private string $file_path;

    private int $regencyId;

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
            'id', 'prov', 'kota_kab', 'kec', 'destination', 'zip_code', 'tier_1', 'tier_2', 'tier_3', 'tier_4', 'tier_5', 'tier_6', 'tier_7', 'tier_8', 'notes'
        ];

        $this->file_path = $this->option('file');
        if (! $this->file_path) {
            $this->error('Option file is required');
            return;
        }

        $this->regencyId = $this->option('regency');
        if (! $this->regencyId) {
            $this->error('Option ID of regency is required');
            return;
        }

        $f = \Illuminate\Support\Facades\File::exists($this->file_path);
        if (! $f) {
            $this->info('file not found');
            return;
        }

        $provinceId = DB::table('geo_regencies')->leftJoin('geo_provinces', 'geo_regencies.province_id', 'geo_provinces.id')->where('geo_regencies.id', $this->regencyId)->first();

        $rows = [];
        $data = $this->csv_to_array($this->file_path, $header);
        foreach ($data as $k => $d) {
            if ($k == 0) {
                continue;
            }

            if (($k % 5000) == 0) {
                $this->info($k);
            }

            $q = "SELECT * FROM geo_sub_districts WHERE name ILIKE '%s' LIMIT 1";
            $q = sprintf($q, pg_escape_string($d['destination']));
            $z = DB::select($q);

            if (count($z) == 0) {
                $m = sprintf('not found: %s', $d['destination']);
                $this->error($m);
                break; // stop!
            }

            $r = $z[0];

            $rows[] = $this->cubicRows($provinceId, $this->regencyId, $r, $d);

            if (count($rows) == 100) {
                try {
                    DB::table('cubic_prices')->insert($rows);
                } catch (\Exception $e) {
                    dd($e);
                }
                $rows = [];
            }
        }

        if (count($rows)) {
            DB::table('cubic_prices')->insert($rows);
            $this->info(count($rows));
        }

        $this->info('Update transit price finished.');
    }

    private function csv_to_array($filename = '', $header)
    {
        $delimiter = ',';
        if (! file_exists($filename) || ! is_readable($filename)) {
            return FALSE;
        }

        $data = [];
        if (($handle = fopen($filename, 'r')) !== FALSE) {
            while (($row = fgetcsv($handle, 0, $delimiter)) !== FALSE) {
                $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }

        return $data;
    }

    /**
     * @return array
     * This rows for inserting to tables
     */
    private function cubicRows($provinceId, $regencyId, $r, $d): array
    {
        return [
            'origin_province_id' => $provinceId->id,
            'origin_regency_id' => $regencyId,
            'destination_id' => $r->id,
            'zip_code' => $d['zip_code'],
            'service_code' => 'tpc',
            'amount' => $d['tier_1'],
            'notes' => $d['notes'],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ];
    }
}
