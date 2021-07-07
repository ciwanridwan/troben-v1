<?php

namespace App\Concerns\Controllers;

use App\Models\Payments\Gateway;

trait HasAdminCharge
{
    /**
     * @param Gateway $gateway
     * @param float|int $amount
     * @return float
     */
    public static function adminChargeCalculator(Gateway $gateway, float $amount = 0): float
    {
        return $gateway->is_fixed
            ? $gateway->admin_charges
            : ceil($gateway->admin_charges * $amount);
    }
}
