<?php

namespace Database\Seeders;

use App\Models\Attachment;
use Illuminate\Database\Seeder;

class AttachmentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Attachment::query()->create([
            'title' => 'receipt.jpg',
            'mime' => 'image/jpeg',
            'disk' => 'receipt',
            'path' => 'dummies/receipt.jpg',
            'type' => 'receipt',
        ]);
    }
}
