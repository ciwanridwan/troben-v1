<?php

namespace App\Models\Payments;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_FAILED = 'failed';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_WAITING_FOR_APPROVAL = 'waiting_for_approval';

    protected $table = 'payments';
}
