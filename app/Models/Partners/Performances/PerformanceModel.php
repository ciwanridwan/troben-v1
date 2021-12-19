<?php

namespace App\Models\Partners\Performances;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PerformanceModel extends Pivot
{
    use HasFactory;

    public const STATUS_ON_PROCESS = 1;
    public const STATUS_FAILED = 5;
    public const STATUS_REACHED = 10;
}
