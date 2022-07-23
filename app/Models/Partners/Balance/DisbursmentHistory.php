<?php

namespace App\Models\Partners\Balance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisbursmentHistory extends Model
{
    use HasFactory;

    /** Define Table */
    protected $table = 'disbursement_histories';

    /** Define column for allow to inserting */
    protected $fillable = [
        'disbursement_id',
        'receipt',
        'amount',
    ];

    
}
