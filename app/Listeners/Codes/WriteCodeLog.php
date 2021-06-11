<?php

namespace App\Listeners\Codes;

use App\Events\CodeScanned;
use App\Events\Packages\PackageCreated;
use App\Events\Packages\PackageUpdated;
use App\Models\CodeLogable;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Package;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Events\Deliveries\Pickup as DeliveryPickup;
use App\Events\Deliveries\Transit as DeliveryTransit;
use App\Events\Packages\PackageAlreadyPackedByWarehouse;
use App\Events\Packages\PackageApprovedByCustomer;
use App\Events\Packages\PackageCanceledByAdmin;
use App\Events\Packages\PackageCheckedByCashier;
use App\Events\Packages\PackageEstimatedByWarehouse;
use App\Events\Packages\PackagePaymentVerified;
use App\Events\Packages\WarehouseIsEstimatingPackage;
use App\Events\Packages\WarehouseIsStartPacking;
use App\Jobs\Codes\Logs\CreateNewLog;
use App\Models\Code;
use App\Models\Partners\Pivot\UserablePivot;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class WriteCodeLog
{
    use DispatchesJobs;
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
            case $event instanceof PackageCreated:
                $package = $event->package;
                $package->refresh();
                $user = auth()->user();
                if (! $user) {
                    $user = $package->customer;
                }
                $this->packageLog(
                    $user,
                    $package,
                    $package->code,
                    [
                        'log_showable' => [CodeLogable::SHOW_ADMIN, CodeLogable::SHOW_CUSTOMER]
                    ]
                );
                break;
            case $event instanceof PackageApprovedByCustomer || $event instanceof PackageCheckedByCashier || $event instanceof PackageUpdated || $event instanceof PackagePaymentVerified || $event instanceof PackageEstimatedByWarehouse || $event instanceof WarehouseIsStartPacking || $event instanceof PackageAlreadyPackedByWarehouse || $event instanceof PackageCanceledByAdmin || $event instanceof WarehouseIsEstimatingPackage:
                $package = $event->package;
                $package->refresh();
                $user = auth()->user();
                if (! $user) {
                    $user = $package->customer;
                }
                $this->packageLog($user, $package, $package->code, [
                    'log_showable' => CodeLogable::SHOW_ALL
                ]);
                break;
            case $event instanceof DeliveryTransit\DriverUnloadedPackageInDestinationWarehouse || $event instanceof DeliveryPickup\DriverUnloadedPackageInWarehouse:
                $delivery = $event->delivery;
                $delivery->refresh();
                $user = auth()->user();
                if (! $user) {
                    $user = $delivery->partner;
                }
                $this->deliveryLog(
                    $user,
                    $delivery,
                    $delivery->code,
                    [
                        'log_showable' => [CodeLogable::SHOW_ADMIN, CodeLogable::SHOW_PARTNER]
                    ]
                );
                break;
            case $event instanceof CodeScanned:
                $delivery = $event->delivery;
                $code = $event->code;
                $role = $event->role;
                $codeable = $code->codeable;
                $package = $codeable instanceof Package ? $codeable : $codeable->package;
                $user = $event->actor;
                if (! $user) {
                    $user = $event->codeable;
                }
                $inputs = [
                    'log_showable' => CodeLogable::SHOW_ALL,
                    'log_type' => CodeLogable::TYPE_SCAN,
                    'log_status' => $role
                ];
                $this->packageLog($delivery->code, $package, $code, $inputs);
                break;
            case $event instanceof DeliveryPickup\PackageLoadedByDriver || $event instanceof DeliveryTransit\PackageLoadedByDriver:
                $delivery = $event->delivery;
                $delivery->refresh();
                $user = auth()->user();
                if (! $user) {
                    $user = $delivery->partner;
                }
                $this->deliveryLog(
                    $user,
                    $delivery,
                    $delivery->code,
                    [
                        'log_showable' => [CodeLogable::SHOW_ADMIN, CodeLogable::SHOW_PARTNER]
                    ]
                );
                $this->deliveryLog($user, $delivery, $delivery->code, [
                    'log_showable' => [CodeLogable::SHOW_ADMIN, CodeLogable::SHOW_PARTNER],
                    'log_type' => CodeLogable::TYPE_SCAN,
                    'log_status' => UserablePivot::ROLE_DRIVER
                ]);
                break;

            default:
                # code...
                break;
        }
    }
    protected function packageLog(Model $model, Package $package, Code $code, $inputs)
    {
        if (! Arr::has($inputs, 'log_description')) {
            $logDescription = CodeLogable::getAvailableStatusCode()[$package->status.'_'.$package->payment_status];
        } else {
            $logDescription = $inputs['log_description'];
        }
        if (! Arr::has($inputs, 'log_status')) {
            $logStatus = $package->status.'_'.$package->payment_status;
        } else {
            $logStatus = $inputs['log_status'];
        }
        if (! Arr::has($inputs, 'log_showable')) {
            $logShowable = CodeLogable::SHOW_ALL;
        } else {
            $logShowable = $inputs['log_showable'];
        }
        if (! Arr::has($inputs, 'log_type')) {
            $logType = CodeLogable::TYPE_INFO;
        } else {
            $logType = $inputs['log_type'];
        }
        $inputs = [
            'type' => $logType,
            'showable' => Arr::wrap($logShowable),
            'status' => $logStatus,
            'description' => $logDescription
        ];

        if ($logType === CodeLogable::TYPE_SCAN) {
            if (! $model->code_logs()->firstWhere($inputs)) {
                $job = new CreateNewLog($code, $model, $inputs);
                $this->dispatch($job);
            }
        } else {
            $job = new CreateNewLog($code, $model, $inputs);
            $this->dispatch($job);
        }
    }

    protected function deliveryLog(Model $model, Delivery $delivery, Code $code, $inputs)
    {
        if (! Arr::has($inputs, 'log_description')) {
            $logDescription = CodeLogable::getAvailableStatusCode()[$delivery->type.'_'.$delivery->status];
        } else {
            $logDescription = $inputs['log_description'];
        }
        if (! Arr::has($inputs, 'log_status')) {
            $logStatus = $delivery->type.'_'.$delivery->status;
        } else {
            $logStatus = $inputs['log_status'];
        }
        if (! Arr::has($inputs, 'log_showable')) {
            $logShowable = CodeLogable::SHOW_ALL;
        } else {
            $logShowable = $inputs['log_showable'];
        }
        if (! Arr::has($inputs, 'log_type')) {
            $logType = CodeLogable::TYPE_INFO;
        } else {
            $logType = $inputs['log_type'];
        }

        $inputs = [
            'type' => $logType,
            'showable' => Arr::wrap($logShowable),
            'status' => $logStatus,
            'description' => $logDescription
        ];

        $job = new CreateNewLog($code, $model, $inputs);
        $this->dispatch($job);
    }
}
