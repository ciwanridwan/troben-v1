<?php

namespace App\Models\Partners\Balance;

use App\Models\Payments\Withdrawal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisbursmentHistory extends Model
{
    use HasFactory;

    /** Define Table */
    protected $table = 'disbursment_histories';

    /** Define attributes for allow to inserting */
    protected $fillable = [
        'disbursment_id',
        'receipt',
        'amount',
    ];

    /**  Define attributes for be hidden as arrays*/
    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
    ];

    /** Declare relation to Withdrawal Models */
    public function disbursment()
    {
        return $this->belongsTo(Withdrawal::class, 'disbursment_id', 'id');
    }
}
