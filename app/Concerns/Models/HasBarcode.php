<?php

namespace App\Concerns\Models;

use App\Models\Order;
use Carbon\Carbon;

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
        if ($this instanceof Order) {
            return $this->generateBarcodeOrder();
        }

        // default
        return $this->getBarcodeType() . Carbon::now()->format('dmy') . random_int(0, 10000);
    }

    private function generateBarcodeOrder()
    {
        $pre = $this->getBarcodeType() . Carbon::now()->format('dmy');
        $last_order = $this->query()->where('barcode', 'LIKE', $pre . '%')->orderBy('barcode', 'desc')->first();
        $inc_number = $last_order ? substr($last_order->barcode, strlen($last_order->barcode)) : 0;
        $inc_number = (int) $inc_number;
        $inc_number = $last_order ? $inc_number + 1 : $inc_number;

        // assume 10.000/day
        $inc_number = str_pad($inc_number, 5, '0', STR_PAD_LEFT);
        return  $pre . $inc_number;
    }
}
