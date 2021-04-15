<?php

namespace App\Observers;

use App\Models\Packages\Package;
use Carbon\Carbon;

class CodeObserver
{
    protected $model;

    function created($model)
    {
        dd(get_class_vars(get_class($model)));
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
        if ($this instanceof Package) {
            return $this->generateBarcodeOrder();
        }

        // default
        return $this->getBarcodeType() . Carbon::now()->format('dmy') . random_int(0, 10000);
    }

    private function generateBarcodeOrder()
    {
        $pre = $this->getBarcodeType() . Carbon::now()->format('dmy');
        $last_order = $this->query()->where('barcode', 'LIKE', $pre . '%')->orderBy('barcode', 'desc')->first();
        $inc_number = $last_order ? substr($last_order->barcode, strlen($pre)) : 0;
        $inc_number = (int) $inc_number;
        $inc_number = $last_order ? $inc_number + 1 : $inc_number;

        // assume 100.000/day
        $inc_number = str_pad($inc_number, 5, '0', STR_PAD_LEFT);

        return  $pre . $inc_number;
    }
}
