<?php

namespace App\Supports\Translates;

use App\Contracts\HasCodeLog;
use App\Models\Deliveries\Delivery;
use App\Models\Geo\Regency;
use App\Models\Packages\Package as PackagesPackage;
use App\Models\Partners\Partner;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class Package implements HasCodeLog
{
    /**
     * @var PackagesPackage $package
     */
    public PackagesPackage $package;
    public function __construct(PackagesPackage $package)
    {
        $this->package = $package;
    }

    /**
     * @return string
     */
    public function translate(): string
    {
        $packageDescriptionFormat = $this->getDescriptionFormat();
        if (empty($packageDescriptionFormat)) {
            return $this->package->status.'_'.$this->package->payment_status;
        }
        // throw_if(!$packageDescriptionFormat, Error::make(Response::RC_CODE_LOG_UNAVAILABLE));

        $packageDescriptionFormat['variable'] = array_flip($packageDescriptionFormat['variable']);

        foreach ($packageDescriptionFormat['variable'] as $key => $value) {
            try {
                $packageDescriptionFormat['variable'][$key] = $this->replacer($key);
            } catch (\Throwable $th) {
                $packageDescriptionFormat['variable'][$key] = '';
                Log::warning('[TRANSLATE] Error Replacer');
            }
        }
        $description = __($packageDescriptionFormat['description'], $packageDescriptionFormat['variable']);

        return $description;
    }

    /**
     * @return array
     */
    public function getDescriptionFormat(): array
    {
        foreach (PackagesPackage::getAvailableDescriptionFormat() as $value) {
            if (in_array($this->package->status, $value['status']) && in_array($this->package->payment_status, $value['payment_status'])) {
                return $value;
            }
        }
        return [];
    }

    /**
     * @return string
     */
    public function replacer(string $replace): string
    {
        switch ($replace) {
            case 'received_by':
                return $this->package->received_by;
            case 'received_at':
                return $this->package->received_at;
            case 'origin_partner_name':
                /** @var Delivery $delivery */
                $delivery = $this->getLastDelivery();
                $origin_partner = $delivery->origin_partner;
                return $origin_partner->name;
            case 'origin_partner_code':
                /** @var Delivery $delivery */
                $delivery = $this->getLastDelivery();
                /** @var Partner $origin_partner */
                $origin_partner = $delivery->origin_partner;
                return $origin_partner->code;
            case 'origin_partner_regency_name':
                /** @var Delivery $delivery */
                $delivery = $this->getLastDelivery();
                /** @var Partner $origin_partner */
                $origin_partner = $delivery->origin_partner;
                /** @var Regency $origin_partner */
                $regency = $origin_partner->regency;
                return $regency->name ?? '';
            case 'partner_name':
                /** @var Delivery $delivery */
                $delivery = $this->getLastDelivery();
                $partner = $delivery->partner;
                return $partner->name;
            case 'partner_code':
                /** @var Delivery $delivery */
                $delivery = $this->getLastDelivery();
                /** @var Partner $partner */
                $partner = $delivery->partner;
                return $partner->code;
            case 'partner_regency_name':
                /** @var Delivery $delivery */
                $delivery = $this->getLastDelivery();
                /** @var Partner $partner */
                $partner = $delivery->partner;
                /** @var Regency $partner */
                $regency = $partner->regency;

                return "($regency->name)" ?? '';
            case 'packager_name':
                /** @var User $packager */
                $packager = $this->package->packager()->first();
                return $packager->name;
            case 'estimator_name':
                /** @var User $estimator */
                $estimator = $this->package->estimator()->first();
                return $estimator->name;
            case 'loader_name':
                /** @var User $loader */
                $loader = auth()->user();
                return $loader->name;
            case 'unloader_name':
                /** @var User unloader */
                $unloader = auth()->user();
                return $unloader->name;
            case 'delivery_code':
                /** @var Delivery $delivery */
                $delivery = $this->getLastDelivery();
                return $delivery->code->content;
            case 'updated_at':
                return $this->package->updated_at->format('d M Y H:i:s');
            case 'destination':
                /** @var Builder $query */
                $query = $this->package->deliveries()->orderByPivot('created_at', 'desc');
                /** @var Delivery $delivery */
                $delivery = $query->first();
                /** @var Partner $partner */
                $partner = $delivery->partner()->first();
                switch ($delivery->type) {
                    case Delivery::TYPE_PICKUP:
                        return "{$partner->name} [{$partner->code}]";
                        break;
                    case Delivery::TYPE_TRANSIT:
                        return "{$partner->name} [{$partner->code}]";
                        break;
                    case Delivery::TYPE_DOORING:
                        return $this->package->receiver_address;
                        break;
                    case Delivery::TYPE_RETURN:
                        return $this->package->sender_address;
                    default:
                        return '';
                }
                return $delivery->code->content;
            default:
                return '';
        }
    }

    public function getLastDeliveryQuery(): Builder
    {
        /** @var Builder $query */
        $query = $this->package->deliveries()->orderByPivot('created_at', 'desc')->getQuery()->with('code');
        return $query;
    }
    public function getLastDelivery()
    {
        return $this->getLastDeliveryQuery()->get()->sortByDesc('id')->first();
    }
}
