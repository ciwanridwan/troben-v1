<?php

namespace App\Concerns\Models;

use Illuminate\Support\Str;

trait UuidAsPrimaryKey
{
    /**
     * Generate UUID as primary key upon creating new record on eloquent model.
     *
     * @return void
     */
    public static function bootUuidAsPrimaryKey()
    {
        self::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = $model->generateUuid();
            }
        });
    }

    /**
     * Generate Uuid.
     *
     * @return string
     * @throws \Exception
     */
    public function generateUuid(): string
    {
        return Str::orderedUuid();
    }
}
