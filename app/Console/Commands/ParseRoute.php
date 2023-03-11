<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ParseRoute extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'core:route';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $header = [
            'kota',
            'prv_dest',
            'reg_dest',
            'dis_dest',
            'note',
            'partner_name_mtak1',
            'partner_code_mtak1',
            'partner_name_mtak1_dest',
            'partner_code_mtak1_dest',
            'partner_name_mtak2',
            'partner_code_mtak2',
            'partner_name_mtak2_dest',
            'partner_code_mtak2_dest',
            'partner_name_mtak3',
            'partner_code_mtak3',
            'partner_name_mtak3_dest',
            'partner_code_mtak3_dest',
            'partner_name_dooring',
            'partner_code_dooring',
        ];

        $file_name = 'mt_template_2.csv';

        $file_path = database_path('csv/'.$file_name);

        $f = \Illuminate\Support\Facades\File::exists($file_path);
        if (! $f) {
            $this->info('file not found: '.$file_path);
            return;
        }

        $this->info('start file: '.$file_name);

        $rows = [];
        $data = $this->csv_to_array($file_path, $header);
        foreach ($data as $k => $d) {
            if ($k == 0) {
                continue;
            }

            // // todo
            // if ($k >= 1100) break;

            $idProv = 0;
            $idRegency = 0;
            $idDistrict = 0;
            if ($d['prv_dest'] != '') {
                if ($d['prv_dest'] == 'NTT') {
                    $d['prv_dest'] = 'Nusa Tenggara Timur';
                }
                if ($d['prv_dest'] == 'NTB') {
                    $d['prv_dest'] = 'Nusa Tenggara Barat';
                }


                $kc = sprintf('prv:%s', $d['prv_dest']);
                if (Cache::has($kc)) {
                    $prov = Cache::get($kc);
                    $idProv = $prov->id;
                } elseif (strtolower($d['prv_dest']) == 'jabodetabek') {
                    $idProv = 9999;
                } else {
                    $q = "SELECT id FROM geo_provinces WHERE name ILIKE '%s'";
                    $q = sprintf($q, $d['prv_dest']);
                    $prov = DB::select($q);
                    if (count($prov) == 0) {
                        $this->error('prov not found: ['.$k.']'.$d['prv_dest']);
                        continue;
                    }
                    $prov = $prov[0];
                    $idProv = $prov->id;
                }
            }
            if ($d['reg_dest'] != '') {
                $kc = sprintf('reg:%s-%d', $d['reg_dest'], $idProv);
                if (Cache::has($kc)) {
                    $regnc = Cache::get($kc);
                    $idRegency = $regnc->id;
                } elseif (strtolower($d['reg_dest']) == 'jabodetabek') {
                    $idRegency = 9999;
                } else {
                    $regnc = $this->getregcode($d['reg_dest'], $idProv);
                    if (is_null($regnc)) {
                        $regnc = $this->getregcode('Kota '.$d['reg_dest'], $idProv);
                        if (is_null($regnc)) {
                            $regnc = $this->getregcode('Kabupaten '.$d['reg_dest'], $idProv);
                            if (is_null($regnc)) {
                                $this->error('reg not found: ['.$k.']'.$idProv.' - "'.$d['reg_dest'].'"');
                                continue;
                            }
                        }
                    }
                    $idRegency = $regnc;
                }
            }
            if ($d['dis_dest'] != '') {
                $kc = sprintf('dis:%s-%d-%d', $d['dis_dest'], $idProv, $idRegency);
                if (Cache::has($kc)) {
                    $distr = Cache::get($kc);
                    $idDistrict = $distr->id;
                } elseif (strtolower($d['dis_dest']) == 'jabodetabek') {
                    $idDistrict = 9999;
                } else {
                    $q = "SELECT id FROM geo_districts WHERE name ILIKE '%s' AND regency_id = %d AND province_id = %d";
                    $q = sprintf($q, $d['dis_dest'], $idRegency, $idProv);
                    $distr = DB::select($q);
                    if (count($distr) == 0) {
                        $this->error('dis not found: ['.$k.']'.$idProv.'-'.$idRegency.' - "'.$d['dis_dest'].'"');
                        continue;
                    }
                    $distr = $distr[0];
                    $idDistrict = $distr->id;
                }
            }

            $rows[] = [
                'warehouse' => $d['kota'],
                'province_id' => $idProv,
                'regency_id' => $idRegency,
                'district_id' => $idDistrict,
                'note' => $d['note'],
                'vendor_mtak_1' => $d['partner_name_mtak1'],
                'code_mtak_1' => $d['partner_code_mtak1'],
                'vendor_mtak_1_dest' => $d['partner_name_mtak1_dest'],
                'code_mtak_1_dest' => $d['partner_code_mtak1_dest'],
                'vendor_mtak_2' => $d['partner_name_mtak2'],
                'code_mtak_2' => $d['partner_code_mtak2'],
                'vendor_mtak_2_dest' => $d['partner_name_mtak2_dest'],
                'code_mtak_2_dest' => $d['partner_code_mtak2_dest'],
                'vendor_mtak_3' => $d['partner_name_mtak3'],
                'code_mtak_3' => $d['partner_code_mtak3'],
                'vendor_mtak_3_dest' => $d['partner_name_mtak3_dest'],
                'code_mtak_3_dest' => $d['partner_code_mtak3_dest'],
                'vendor_dooring' => $d['partner_name_dooring'],
                'code_dooring' => $d['partner_code_dooring'],
            ];
        }

        $this->info('get rows: '.count($rows));
        // $rowInsert = [];
        // foreach ($rows as $r) {
        //     $rowInsert[] = $r;

        //     if (count($rowInsert) == 100) {
        //         $this->info('get rows: '.count($rowInsert));
        //         $this->batchInsert($rowInsert);
        //         $rowInsert = [];
        //     }
        // }
        // if (count($rowInsert)) {
        //     $this->info('get rows: '.count($rowInsert));
        //     $this->batchInsert($rowInsert);
        //     $this->info(count($rowInsert));
        // }

        return Command::SUCCESS;
    }

    private function getregcode($id_reg, $id_prov)
    {
        $q = "SELECT id FROM geo_regencies WHERE name ILIKE '%s' AND province_id = %d";
        $q = sprintf($q, $id_reg, $id_prov);
        $regnc = DB::select($q);
        if (count($regnc) == 0) {
            return null;
        }
        return $regnc[0]->id;
    }

    private function batchInsert($rows)
    {
        try {
            DB::table('transport_routes')->insert($rows);
        } catch (\Exception $e) {
            dd($e);
        }
    }

    private function csv_to_array($filename = '', $header)
    {
        $delimiter = ';';
        if (! file_exists($filename) || ! is_readable($filename)) {
            return FALSE;
        }

        $data = [];
        if (($handle = fopen($filename, 'r')) !== FALSE) {
            while (($row = fgetcsv($handle, 0, $delimiter)) !== FALSE) {
                if (count($header) != count($row)) {
                    dd($header, $row);
                }
                $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }

        return $data;
    }
}
