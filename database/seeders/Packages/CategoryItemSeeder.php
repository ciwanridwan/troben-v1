<?php

namespace Database\Seeders\Packages;

use App\Models\Packages\CategoryItem;
use Illuminate\Database\Seeder;

class CategoryItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            'Pakaian',
            'Kosmetik',
            'Aksesoris',
            'Makanan Non Frozen',
            'Elektronik',
            'Perabotan',
            'Motor',
            'Lainnya'
        ];

        for ($i=0; $i < count($data); $i++) {
            CategoryItem::create([
                'name' => $data[$i]
            ]);
        }
    }
}
