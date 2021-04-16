<?php

namespace App\Concerns\Models;

use App\Jobs\Code\CreateNewCode;
use App\Jobs\Code\UpdateExistingCode;
use App\Models\Code;
use App\Models\Packages\Item;
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
        self::updated(function ($model) {
            if ($model instanceof Item) {
                $job = new UpdateExistingCode($model);
                $model->dispatch($job);
            }
        });
    }
}
