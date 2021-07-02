<?php

namespace App\Supports\Translates;

use App\Contracts\HasCodeLog;
use App\Models\Packages\Package as PackagesPackage;

class Package implements HasCodeLog
{
    /**
     * @var App\Models\Packages\Package
     */
    public PackagesPackage $package;
    function __construct(PackagesPackage $package)
    {
        $this->package = $package;
    }

    /**
     * @return string
     */
    public function translate(): string
    {
        $packageDescriptionFormat = $this->getDescriptionFormat();
        if (!$packageDescriptionFormat) {
            return $this->package->status . '_' . $this->package->payment_status;
        }
        // throw_if(!$packageDescriptionFormat, Error::make(Response::RC_CODE_LOG_UNAVAILABLE));

        $packageDescriptionFormat['variable'] = array_flip($packageDescriptionFormat['variable']);

        foreach ($packageDescriptionFormat['variable'] as $key => $value) {
            $packageDescriptionFormat['variable'][$key] = $this->replacer($key);
        }
        $description = __($packageDescriptionFormat['description'], $packageDescriptionFormat['variable']);

        return $description;
        return '';
    }

    /**
     * @return array
     */
    public function getDescriptionFormat(): array
    {
        foreach (PackagesPackage::getAvailableDescriptionFormat() as $key => $value) {
            if (in_array($this->package->status, $value['status']) && in_array($this->package->payment_status, $value['payment_status'])) {
                return $value;
            }
        }
        return false;
    }

    /**
     * @return string
     */
    public function replacer(string $replace): string
    {
        switch ($replace) {
            case 'partner_name':
                /** @var Delivery $pickupDelivery */
                $pickupDelivery = $this->package->picked_up_by()->first();
                /** @var Partner $partner */
                $partner = $pickupDelivery->partner;
                return $partner->name;

            default:
                return '';
        }
    }
}
