<?php
namespace App\Concerns\Controllers;

use App\Models\Payments\Gateway;

trait HasAdminCharge
{
    /**
     * @param string $channel
     * @param float|int $amount is not required for fix charge
     * @return float
     */
    public static function adminChargeCalculator(Gateway $gateway, float $amount = 0): float
    {
        return $gateway->is_fixed
            ? $gateway->admin_charges
            : $gateway->admin_charges * $amount / 100;
    }
}
