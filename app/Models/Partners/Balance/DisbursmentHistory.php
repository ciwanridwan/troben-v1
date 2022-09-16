<?php

namespace App\Models\Partners\Balance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisbursmentHistory extends Model
{
    use HasFactory;

    public const STATUS_APPROVE = 'approve';
    public const STATUS_WAITING_FOR_APPROVE = 'waiting_for_approve';

    /** Define Table */
    protected $table = 'disbursment_histories';

    /** Define column for allow to inserting */
    protected $fillable = [
        'disbursment_id',
        'receipt',
        'amount',
        'status'
    ];

    public static function getAvailableStatus(): array
    {
        return [
            self::STATUS_APPROVE,
            self::STATUS_WAITING_FOR_APPROVE
        ];
    }
}
