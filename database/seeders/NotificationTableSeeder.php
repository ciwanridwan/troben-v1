<?php

namespace Database\Seeders;

use App\Models\Notifications\Notification;
use Illuminate\Database\Seeder;

class NotificationTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [[
            'type' => Notification::TYPE_CUSTOMER_HAS_PAID,
            'data' => [
                'title' => 'Order :package_code',
                'body' => 'Pembayaran anda sudah diverifikasi.',
                'variable' => ['package_code'],
            ],
            'priority' => 'normal',
        ],[
            'type' => Notification::TYPE_PARTNER_BALANCE_UPDATED,
            'data' => [
                'title' => 'Cek Saldomu!',
                'body' => 'Saldomu sudah terupdate. Ayo cek sekarang!',
            ],
            'priority' => 'normal',
        ]];

        collect($data)->each(fn ($v) => Notification::create($v));
    }
}
