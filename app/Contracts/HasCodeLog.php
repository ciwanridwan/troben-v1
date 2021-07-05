<?php

namespace App\Contracts;

/**
 * Interface HasOtpToken.
 *
 * @property-read App\Models\Code|App\Models\Deliveries\Delivery|App\Models\Packages|App\Models\Packages\Item
 */
interface HasCodeLog
{
    /**
     * @return string
     */
    public function translate(): string;

    /**
     * @return array
     */
    public function getDescriptionFormat(): array;

    /**
     * @return string
     */
    public function replacer(string $replace): string;
}
