<?php

namespace App\Exports;

use App\Models\Withdrawal;
use Maatwebsite\Excel\Concerns\FromCollection;

class DisbursmentExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Withdrawal::all();
    }
}
