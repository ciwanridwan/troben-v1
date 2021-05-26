<?php

namespace App\Listeners\Codes;

use App\Events\Codes\CodeCreated;
use App\Events\CodeScanned;
use App\Models\Code;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Item;
use App\Models\Packages\Package;
use App\Models\Partners\Pivot\UserablePivot;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class UpdateOrCreateScannedCode
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        switch (true) {
            case $event instanceof CodeCreated:
                $this->initScannedField($event->code);
                break;
            case $event instanceof CodeScanned:
                $this->updateScannedField($event);
                break;

            default:
                # code...
                break;
        }
    }
    protected function initScannedField(Code $code)
    {
        switch (true) {
            case $code->codeable instanceof Delivery:
                $scanned = [
                    UserablePivot::ROLE_DRIVER => [
                        'is_scanned' => false,
                        'scanned_at' => null,
                        Code::TYPE_ITEM => []
                    ],
                    UserablePivot::ROLE_WAREHOUSE => [
                        'is_scanned' => false,
                        'scanned_at' => null,
                        Code::TYPE_RECEIPT => [],
                        Code::TYPE_ITEM => []
                    ],
                ];
                $code->scanned = json_encode($scanned);
                $code->save();
                return $code;
            case $code->codeable instanceof Package:

                $scanned = [
                    UserablePivot::ROLE_WAREHOUSE => [],
                ];
                $code->scanned = json_encode($scanned);
                $code->save();
                return $code;

            case $code->codeable instanceof Item:
                $item_codes = $code->codeable->package->items->pluck('codes')->flatten(1);

                $scanned = [
                    UserablePivot::ROLE_DRIVER => [],
                    UserablePivot::ROLE_WAREHOUSE => [],
                ];
                $item_codes->each(function ($item) use ($scanned) {
                    $item->scanned = json_encode($scanned);
                    $item->save();
                });
                return $code;

            default:
                # code...
                break;
        }
    }
    protected function UpdateScannedField($event)
    {
        switch (true) {
            case $event->role === UserablePivot::ROLE_DRIVER:
                $this->driverUpdateScannedField($event->code, $event->delivery);
                break;
            case $event->role === UserablePivot::ROLE_WAREHOUSE:
                $this->warehouseUpdateScannedField($event->code, $event->delivery);
                break;

            default:
                # code...
                break;
        }
    }
    protected function driverUpdateScannedField(Code $code, Delivery $delivery)
    {
        if ($code->codeable instanceof Package || $code->codeable instanceof Item) {
            $this->deliveryUpdateScannedField(UserablePivot::ROLE_DRIVER, $delivery, $code);
            $this->PackageOrItemUpdateScannedField(UserablePivot::ROLE_DRIVER, $delivery, $code);
        }
    }
    protected function warehouseUpdateScannedField(Code $code, Delivery $delivery)
    {
        if ($code->codeable instanceof Package || $code->codeable instanceof Item) {
            $this->deliveryUpdateScannedField(UserablePivot::ROLE_WAREHOUSE, $delivery, $code);
            $this->PackageOrItemUpdateScannedField(UserablePivot::ROLE_WAREHOUSE, $delivery, $code);
        }
    }
    protected function deliveryUpdateScannedField(string $role, Delivery $delivery, Code $code)
    {
        $codeType = null;
        switch (true) {
            case $code->codeable instanceof Package:
                $codeType = Code::TYPE_RECEIPT;
                break;
            case $code->codeable instanceof Item:
                $codeType = Code::TYPE_ITEM;
                break;
            default:
                $codeType = Code::TYPE_RECEIPT;
                break;
        }

        $deliveryScans = collect($delivery->code->scanned)->toArray();

        $fieldScans = collect($deliveryScans[$role]->$codeType)->toArray();
        if (! Arr::has($fieldScans, $code->content)) {
            $fieldScans[$code->content] = [
                'is_scanned' => true,
                'scanned_at' => Carbon::now()
            ];
            $deliveryScans[$role]->is_scanned = true;
            $deliveryScans[$role]->scanned_at = Carbon::now();
            $deliveryScans[$role]->$codeType = $fieldScans;
            $delivery->code->scanned = json_encode($deliveryScans);
            $delivery->code->save();
        }
    }
    protected function PackageOrItemUpdateScannedField(string $role, Delivery $delivery, Code $code)
    {
        $fieldScans = collect($code->scanned)->toArray();

        if (! array_search($delivery->code->content, $fieldScans[$role])) {
            $fieldScans[$role][] = $delivery->code->content;
            $code->scanned = json_encode($fieldScans);
            $code->save();
        }
    }
}
