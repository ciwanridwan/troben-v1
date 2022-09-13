<?php

namespace App\Listeners\Codes;

use App\Events\CodeScanned;
use App\Events\Deliveries\Transit\WarehouseUnloadedPackage;
use App\Events\Packages\PackageCreated;
use App\Events\Packages\PackageUpdated;
use App\Events\Payment\Nicepay\PayByNicepay;
use App\Models\CodeLogable;
use App\Models\Customers\Customer;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Package;
use App\Models\User;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Events\Deliveries\Pickup as DeliveryPickup;
use App\Events\Deliveries\Transit as DeliveryTransit;
use App\Events\Packages\PackageAlreadyPackedByWarehouse;
use App\Events\Packages\PackageApprovedByCustomer;
use App\Events\Packages\PackageCanceledByAdmin;
use App\Events\Packages\PackageCheckedByCashier;
use App\Events\Packages\PackageCreatedForBike;
use App\Events\Packages\PackageEstimatedByWarehouse;
use App\Events\Packages\PackagePaymentVerified;
use App\Events\Packages\WarehouseIsEstimatingPackage;
use App\Events\Packages\WarehouseIsStartPacking;
use App\Jobs\Codes\Logs\CreateNewLog;
use App\Models\Code;
use App\Models\Partners\Pivot\UserablePivot;
use App\Supports\Translates\Translate;
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
    }


    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        if (! $this->checkDatabaseConnection()) {
            return true;
        }

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
                case $event instanceof PackageCreatedForBike:
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
                $this->packageLog($user, $package, $package->code);
                break;
            case $event instanceof PayByNicepay:
                $package = $event->package;
                $package->refresh();

                if ($package->status !== Package::STATUS_WAITING_FOR_PACKING && $package->payment_status === Package::PAYMENT_STATUS_PAID) {
                    break;
                }

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
                $user = auth()->user();
                if (! $user) {
                    $user = $delivery->partner;
                }
                $inputs = [
                    'log_type' => CodeLogable::TYPE_SCAN,
                    'log_status' => $role
                ];
                if ($delivery->type === Delivery::TYPE_DOORING) {
                    $inputs['log_description'] = 'Paket sedang dikirim ke penerima';
                }
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
                $driver_name = $delivery->driver->name;
                $partner_code = $delivery->partner->code;
                foreach ($delivery->packages as $package) {
                    $this->packageLog($user, $package, $package->code, [
                        'log_description' => 'Paket telah berangkat untuk diantar ke Mitra '.$partner_code.' oleh driver '.$driver_name,
                        'log_status' => 'driver_load',
                        'log_showable' => CodeLogable::SHOW_ALL
                    ]);
                }
                break;
            case $event instanceof WarehouseUnloadedPackage:
                $delivery = $event->delivery;
                $package = $event->package;
                $role = $event->role;
                $inputs = [
                    'log_type' => CodeLogable::TYPE_SCAN,
                    'log_status' => $role
                ];
                $this->packageLog($delivery->code, $package, $package->code, $inputs);
                break;
            default:
                # code...
                break;
        }
    }

    /**
     * @param Model $model for polymorp relation
     * @param Package $package instance package model
     * @param Code $code reference to code
     * @param array $inputs
     */
    protected function packageLog(Model $model, Package $package, Code $code, array $inputs = [])
    {
        if (! Arr::has($inputs, 'log_description')) {
            $logDescription = (new Translate($package))->translate();
        } else {
            $logDescription = $inputs['log_description'];
        }
        if (! Arr::has($inputs, 'log_status')) {
            $logStatus = $package->status.'_'.$package->payment_status;
        } else {
            $logStatus = $inputs['log_status'];
        }
        if (! Arr::has($inputs, 'log_showable')) {
            $logShowable = $this->getLogShowableByStatus($logStatus);
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

        if (! $this->checkLogged($code, $model, $inputs)) {
            if ($logType === CodeLogable::TYPE_SCAN) {
                // if (! $model->code_logs()->getQuery()->whereJsonContains('showable', $inputs['showable'])->firstWhere(Arr::except($inputs, 'showable'))) {
                $job = new CreateNewLog($code, $model, $inputs);
                $this->dispatch($job);
            // }
            } else {
                $job = new CreateNewLog($code, $model, $inputs);
                $this->dispatch($job);
            }
        }
    }

    protected function deliveryLog(Model $model, Delivery $delivery, Code $code, $inputs)
    {
        if (! Arr::has($inputs, 'log_description')) {
            $logDescription = (new Translate($delivery))->translate();
        } else {
            $logDescription = $inputs['log_description'];
        }
        if (! Arr::has($inputs, 'log_status')) {
            $logStatus = $delivery->type.'_'.$delivery->status;
        } else {
            $logStatus = $inputs['log_status'];
        }
        if (! Arr::has($inputs, 'log_showable')) {
            $logShowable = $this->getLogShowableByStatus($logStatus);
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

    protected function checkDatabaseConnection()
    {
        $connection = config('database.default');

        $driver = config("database.connections.{$connection}.driver");

        if ($driver === 'sqlite') {
            return false;
        }
        return true;
    }

    /**
     * @param Code $code
     * @param Model|User|Customer $model
     * @param $inputs
     * @return bool
     */
    protected function checkLogged(Code $code, Model $model, $inputs): bool
    {
        $log = $model->code_logs()->where(array_merge(Arr::except($inputs, 'showable'), ['code_id' => $code->id]))->first();
        if (! is_null($log)) {
            $log->touch();
        }
        return ! is_null($log);
    }

    /**
     * Get log showable by status.
     *
     * @param string $status
     * @return array|string[]
     */
    protected function getLogShowableByStatus(string $status): array
    {
        switch ($status):
            case in_array($status, [
                    CodeLogable::STATUS_CREATED_DRAFT,
                    CodeLogable::STATUS_WAITING_FOR_APPROVAL_DRAFT,
                    CodeLogable::STATUS_ACCEPTED_PENDING,
                    CodeLogable::STATUS_ESTIMATING_DRAFT,
                    CodeLogable::STATUS_ESTIMATED_DRAFT,
                    CodeLogable::STATUS_WAITING_FOR_PACKING_PAID,
                    CodeLogable::STATUS_PACKING_PAID,
                    CodeLogable::STATUS_PACKED_PAID,
                    CodeLogable::STATUS_DRIVER_LOAD,
                    CodeLogable::STATUS_WAREHOUSE_UNLOAD,
                    CodeLogable::STATUS_DRIVER_DOORING_LOAD,
                    CodeLogable::STATUS_DELIVERED_PAID
                ]):
                return CodeLogable::SHOW_ALL;
            default:
                return [
                    CodeLogable::SHOW_ADMIN,
                    CodeLogable::SHOW_PARTNER
                ];
        endswitch;
    }
}
