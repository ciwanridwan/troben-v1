<?php

namespace App\Concerns\Models;

use Carbon\Carbon;
use Illuminate\Support\Str;

trait HasBarcode
{
    public static function bootHasBarcode()
    {
        self::creating(function ($model) {
            if (empty($model->{$model->getBarcodeColumn()})) {
                $model->{$model->getBarcodeColumn()} = $model->generateBarcode();
            }
        });
    }

    public function getBarcodeType()
    {
        return property_exists($this, 'barcodeType') ? $this->barcodeType : 'BRC';
    }

    public function getBarcodeColumn()
    {
        return property_exists($this, 'barcodeColumn') ? $this->barcodeColumn : 'barcode';
    }

    public function generateBarcode()
    {
        // kode asal - kode tujuan - GENERATE - INCREMENT
        return $this->getBarcodeType() . Carbon::now()->format('dmy') . random_int(0, 10000);
    }
}
