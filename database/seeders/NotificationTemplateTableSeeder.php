<?php

namespace Database\Seeders;

use App\Models\Notifications\Template;
use Illuminate\Database\Seeder;

class NotificationTemplateTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [[
            'type' => Template::TYPE_CUSTOMER_HAS_PAID,
            'data' => [
                'title' => 'Order :package_code',
                'body' => 'Pembayaran anda sudah diverifikasi.',
                'variable' => ['package_code'],
            ],
            'priority' => 'normal',
        ],[
            'type' => Template::TYPE_PARTNER_BALANCE_UPDATED,
            'data' => [
                'title' => 'Cek Saldomu!',
                'body' => 'Saldomu sudah terupdate. Ayo cek sekarang!',
            ],
            'priority' => 'normal',
        ],[
            'type' => Template::TYPE_CS_GET_NEW_ORDER,
            'data' => [
                'title' => 'Order baru!',
                'body' => 'Ada orderan baru dengan nomor :package_code',
                'variable' => ['package_code'],
            ],
            'priority' => 'normal',
        ]];

        collect($data)->each(fn ($v) => Template::create($v));
    }
}
