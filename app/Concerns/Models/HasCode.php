<?php

namespace App\Concerns\Models;

use App\Models\Packages\Item;
use App\Jobs\Code\CreateNewCode;
use App\Jobs\Code\UpdateExistingCode;
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
