<?php

namespace Database\Seeders;

use App\Models\Payments\Gateway;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;
use League\Csv\Statement;

class PaymentGatewaySeeder extends Seeder
{

    /**
     * @param $filePath
     * @return \Illuminate\Support\Collection
     * @throws \League\Csv\Exception
     */
    public function loadFiles($filePath): Collection
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
        $path_file = __DIR__ . '/data/payment_gateway_list.csv';
        $gateways = $this->loadFiles($path_file);

        $this->command->info("\n\nImport Payment Gateway List Data from " . $path_file);
        foreach ($gateways as $item) {
            DB::transaction(function () use ($item) {
                $g = new Gateway();
                $g->fill([
                    'channel' => $item['channel'],
                    'name' => $item['name'],
                    'admin_charges' => (float)$item['admin_charges'],
                    'is_bank_transfer' => (bool)$item['is_bank_transfer'],
                    'account_bank' => $item['account_bank'],
                    'account_number' => $item['account_number'],
                    'auto_approve' => (bool)$item['auto_approve'],
                    'is_active' => (bool)$item['is_active'],
                    'options' => json_encode(''),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
                $g->save();
            });
        }

        $this->command->table(
            [
                'channel',
                'name',
                'admin_charges',
                'is_bank_transfer',
                'account_bank',
                'account_number',
                'auto_approve',
                'is_active',
            ],
            $gateways
        );
    }
}
