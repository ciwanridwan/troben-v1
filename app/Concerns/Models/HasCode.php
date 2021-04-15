<?php

namespace App\Concerns\Models;

use App\Jobs\Code\CreateNewCode;
use App\Models\Code;
use Carbon\Carbon;
use App\Models\Packages\Package;
use Illuminate\Foundation\Bus\DispatchesJobs;

trait HasCode
{
    use DispatchesJobs;

    public static function bootHasCode()
    {
        self::creating(function ($model) {
            $model->barcode = 'DUMMY';
        });
        self::created(function ($model) {
            $job = new CreateNewCode($model);
            $model->dispatch($job);
        });
    }
}
