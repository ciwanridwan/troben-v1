<?php

namespace App\Models\Partners\Balance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisbursmentHistory extends Model
{
    use HasFactory;

    /** Define Table */
    protected $table = 'disbursment_histories';
}
