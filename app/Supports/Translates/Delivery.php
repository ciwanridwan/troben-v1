<?php

namespace App\Supports\Translates;

use App\Contracts\HasCodeLog;
use App\Models\Deliveries\Delivery as DeliveriesDelivery;
use App\Models\Partners\Partner;
use App\Models\Partners\Transporter;
use App\Models\User;

class Delivery implements HasCodeLog
{
    public DeliveriesDelivery $delivery;

    /**
     * @param App\Models\Deliveries\Delivery $delivery
     */
    public function __construct(DeliveriesDelivery $delivery)
    {
        $this->delivery = $delivery;
    }
    public function translate(): string
    {
        $deliveryDescriptionFormat = $this->getDescriptionFormat();
        if (! $deliveryDescriptionFormat) {
            return $this->delivery->status.'_'.$this->delivery->payment_status;
        }

        $deliveryDescriptionFormat['variable'] = array_flip($deliveryDescriptionFormat['variable']);

        foreach ($deliveryDescriptionFormat['variable'] as $key => $value) {
            $deliveryDescriptionFormat['variable'][$key] = $this->replacer($key);
        }

        $description = __($deliveryDescriptionFormat['description'], $deliveryDescriptionFormat['variable']);

        return $description;
    }

    public function getDescriptionFormat(): array
    {
        foreach (DeliveriesDelivery::getAvailableDescriptionFormat() as $key => $value) {
            if (in_array($this->delivery->type, $value['type']) && in_array($this->delivery->status, $value['status'])) {
                return $value;
            }
        }
        return [];
    }

    public function replacer(string $replace): string
    {
        switch ($replace) {
            case 'origin_partner_code':
                /** @var Partner $origin_partner */
                $origin_partner = $this->delivery->origin_partner;
                return $origin_partner->code;
            case 'origin_partner_name':
                /** @var Partner $origin_partner */
                $origin_partner = $this->delivery->origin_partner;
                return $origin_partner->name;
            case 'partner_code':
                /** @var Partner $partner */
                $partner = $this->delivery->partner;
                return $partner->code;
            case 'partner_name':
                /** @var Partner $partner */
                $partner = $this->delivery->partner;
                return $partner->name;
            case 'driver_name':
                /** @var User $driver */
                $driver = $this->delivery->driver;
                return $driver->name;
            case 'loader_name':
                /** @var User $loader */
                $loader = auth()->user();
                return $loader->name;
            case 'unloader_name':
                /** @var User unloader */
                $unloader = auth()->user();
                return $unloader->name;
            case 'transporter_registration_number':
                /** @var Transporter $transporter */
                $transporter = $this->delivery->transporter()->first();
                return $transporter->registration_number;
            default:
                return '';
        }
    }
}
