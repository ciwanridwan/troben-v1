<?php

namespace App\Supports\Translates;

use App\Models\Packages\Package as PackagesPackage;

class Package
{

    public static function translatePackage(PackagesPackage $package)
    {
        $packageDescriptionFormat = self::getPackageDescriptionFormat($package);
        if (!$packageDescriptionFormat) {
            return $package->status . '_' . $package->payment_status;
        }
        // throw_if(!$packageDescriptionFormat, Error::make(Response::RC_CODE_LOG_UNAVAILABLE));

        foreach ($packageDescriptionFormat['variable'] as $key => $value) {
            $packageDescriptionFormat['variable'][$key] = self::packageReplacer($package, $key);
        }
        $description = __($packageDescriptionFormat['description'], $packageDescriptionFormat['variable']);

        return $description;
    }

    public static function getPackageDescriptionFormat(PackagesPackage $package)
    {
        foreach (PackagesPackage::getAvailableDescriptionFormat() as $key => $value) {
            if (in_array($package->status, $value['status']) && in_array($package->payment_status, $value['payment_status'])) {
                return $value;
            }
        }
        return false;
    }

    public static function packageReplacer(PackagesPackage $package, string $replace)
    {
        switch ($replace) {
            case 'partner_name':
                /** @var Delivery $pickupDelivery */
                $pickupDelivery = $package->picked_up_by()->first();
                /** @var Partner $partner */
                $partner = $pickupDelivery->partner;
                return $partner->name;

            default:
                return '';
        }
    }
}
