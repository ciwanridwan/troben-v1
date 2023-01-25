<?php

namespace App\Actions\Deliveries;

use App\Models\Deliveries\DeliveryRoute;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;

class Route
{
    // list of warehouse
    public const WAREHOUSE_NAROGONG = ['MPW-JKT-01'];

    public const WAREHOUSE_PONTIANAK = ['MB-PNK-01'];

    public const WAREHOUSE_BANDUNG = ['MB-BDG-01', 'MB-BDG-02', 'MB-BDG-04'];

    public const WAREHOUSE_PEKANBARU = ['MB-PKU-02'];

    public const WAREHOUSE_SEMARANG = ['MPW-SMG-01'];

    public const WAREHOUSE_SURABAYA = ['MPW-BJM-01', 'MB-SUB-01'];

    public const WAREHOUSE_TEGAL = ['MPW-TGL-01'];

    public const WAREHOUSE_BANJARMASIN = ['MB-BDJ-03', 'MB-BDJ-02', 'MB-BJM-02'];

    public const WAREHOUSE_MAKASSAR = ['MPW-UPG-03'];

    public const WAREHOUSE_MATARAM = ['MB-MTR-01'];

    public const WAREHOUSE_AMBON = ['MB-AMB-01'];
    // end list

    /** To generate route for some packages */
    public static function generate($partner, $packageHash)
    {
        $check = false;
        $partnerCode = null;

        foreach (self::listWarehouse() as $key => $value) {
            if (in_array($partner->code, $value)) {
                $check = true;
            }
        }

        if ($check) {
            $packages = self::getPackages($packageHash);
            $packages->each(function ($q) use ($partner) {
                $warehouse = self::getWarehousePartner($partner->code, $q);
                switch (true) {
                    case $warehouse instanceof SupportCollection:
                        $dooringPartner = self::getDooringPartner($warehouse[0]->code_dooring);
                        $nextDestination = self::getNextDestination($warehouse->toArray());
                        $regencyId = $warehouse[0]->regency_id;
                        break;
                    default:
                        $dooringPartner = self::getDooringPartner($warehouse->code_dooring);
                        $nextDestination = self::getNextDestination($warehouse);
                        $regencyId = $warehouse->regency_id;
                        break;
                }

                $checkPackages = DeliveryRoute::query()->where('package_id', $q->id)->first();
                if (is_null($checkPackages)) {
                    DeliveryRoute::create([
                        'package_id' => $q->id,
                        'regency_origin_id' => $partner->geo_regency_id,
                        'origin_warehouse_id' => $partner->id,
                        'regency_destination_1' => $regencyId,
                        'regency_destination_2' => is_array($nextDestination) ? $nextDestination['second'] : null,
                        'regency_destination_3' => is_array($nextDestination) ? $nextDestination['third'] : null,
                        'regency_dooring_id' => $dooringPartner->geo_regency_id,
                        'partner_dooring_id' => $dooringPartner->id
                    ]);
                }
            });

            $partnerByRoutes = [];
            foreach ($packages as $package) {
                $partnerByRoute = self::setPartners($package->deliveryRoutes);
                array_push($partnerByRoutes, $partnerByRoute);
            }

            $partnerCode = $partnerByRoutes;
        } else {
            $partnerCode = self::getWarehouseNearby($partner);
        }
        return $partnerCode;
    }

    /**
     * convert hash and get packages
     */
    public static function getPackages($hash): Collection
    {
        $packagesId = [];
        for ($i = 0; $i < count($hash['package_hash']); $i++) {
            $packageId = Package::hashToId($hash['package_hash'][$i]);
            array_push($packagesId, $packageId);
        }

        $packages = Package::query()->whereIn('id', $packagesId)->get();
        return $packages;
    }

    /** To determine package can generate route or not
     */
    public static function checkPackages($hash): bool
    {
        $setPartner = false;
        $packagesId = [];

        for ($i = 0; $i < count($hash['package_hash']); $i++) {
            $packageId = Package::hashToId($hash['package_hash'][$i]);
            array_push($packagesId, $packageId);
        }

        $packages = Package::query()->whereIn('id', $packagesId)->get();
        foreach ($packages as $key => $value) {
            $route = $value->deliveryRoutes;
            if (!is_null($route)) {
                $setPartner = true;
            }
        }

        return $setPartner;
    }

    /**
     * Get warehouse partner for a depedency delivery routes
     */
    public static function getWarehousePartner($partnerCode, $package)
    {
        $regencyId = $package->destination_regency_id;
        $provinceId = $package->destination_regency->province_id;

        switch (true) {
            case in_array($partnerCode, self::WAREHOUSE_NAROGONG):
                $warehouse =  'NAROGONG';
                break;
            case in_array($partnerCode, self::WAREHOUSE_AMBON):
                $warehouse = 'AMBON';
                break;
            case in_array($partnerCode, self::WAREHOUSE_BANDUNG):
                $warehouse = 'BANDUNG';
                break;
            case in_array($partnerCode, self::WAREHOUSE_BANJARMASIN):
                $warehouse = 'BANJARMASIN';
                break;
            case in_array($partnerCode, self::WAREHOUSE_MAKASSAR):
                $warehouse = 'MAKASSAR';
                break;
            case in_array($partnerCode, self::WAREHOUSE_MATARAM):
                $warehouse = 'MATARAM';
                break;
            case in_array($partnerCode, self::WAREHOUSE_PEKANBARU):
                $warehouse = 'PEKANBARU';
                break;
            case in_array($partnerCode, self::WAREHOUSE_PONTIANAK):
                $warehouse = 'PONTIANAK';
                break;
            case in_array($partnerCode, self::WAREHOUSE_SEMARANG):
                $warehouse = 'SEMARANG';
                break;
            case in_array($partnerCode, self::WAREHOUSE_SURABAYA):
                $warehouse = 'SURABAYA';
                break;
            case in_array($partnerCode, self::WAREHOUSE_TEGAL):
                $warehouse = 'TEGAL';
                break;

            default:
                $warehouse = null;
                break;
        }

        $partner = DB::table('transport_routes')->where('warehouse', $warehouse)->where('regency_id', $regencyId)->orWhere(function ($q) use ($warehouse, $provinceId) {
            $q->where('warehouse', $warehouse);
            $q->where('regency_id', 0);
            $q->where('province_id', $provinceId);
        })->first();

        if ($partner->note) {
            $partner = DB::table('transport_routes')->where('warehouse', $warehouse)->where('regency_id', $regencyId)->orWhere(function ($q) use ($warehouse, $provinceId) {
                $q->where('warehouse', $warehouse);
                $q->where('regency_id', 0);
                $q->where('province_id', $provinceId);
            })->get();
        }

        if ($partner instanceof SupportCollection) {
            return $partner->isNotEmpty() ? $partner : null;
        } else {
            return $partner ? $partner : null;
        }
    }

    public static function getNextDestination($warehouse): array|null
    {
        $result = null;
        $destination = null;
        $partner = Partner::query();

        if (is_array($warehouse)) {
            $code = collect($warehouse)->map(function ($q) {
                return [
                    'mtak_2' => $q->code_mtak_2_dest,
                    'mtak_3' => $q->code_mtak_3_dest
                ];
            })->toArray();
            $secondDestination = $partner->where('code', $code[0]['mtak_2'])->first();
            $thirdDestination = Partner::query()->where('code', $code[1]['mtak_3'])->first();
        } else {
            $secondDestination = $partner->where('code', $warehouse->code_mtak_2_dest)->first();
            $thirdDestination = Partner::query()->where('code', $warehouse->code_mtak_3_dest)->first();
        }

        $destination = [
            'second' => $secondDestination ? $secondDestination->geo_regency_id : null,
            'third' => $thirdDestination ? $thirdDestination->geo_regency_id : null
        ];

        return $destination;
    }

    /**
     * Get dooring partner
     */
    public static function getDooringPartner($code): Model|null
    {
        $partner = Partner::query()->where('code', $code)->first();
        return $partner ? $partner : null;
    }

    /**
     * Set partner to show in list
     */
    public static function setPartners($deliveryRoutes)
    {
        $provinceId = $deliveryRoutes->packages->destination_regency->province_id;
        $regencyId = $deliveryRoutes->regency_destination_1;
        $partner = null;

        $warehouse = self::checkWarehouse($deliveryRoutes);

        if ($deliveryRoutes->regency_destination_1 === 0) {
            $partner = DB::table('transport_routes')->where('province_id', $provinceId)->where('warehouse', $warehouse)->first();
        } else {
            $partner = DB::table('transport_routes')->where('regency_id', $regencyId)->where('warehouse', $warehouse)->first();
        }

        switch (true) {
            case is_null($deliveryRoutes->reach_destination_1_at):
                return $partner->code_mtak_1_dest;
                break;
            case is_null($deliveryRoutes->reach_destination_2_at):
                return $partner->code_mtak_2_dest;
                break;
            case is_null($deliveryRoutes->reach_destination_3_at):
                return $partner->code_mtak_3_dest;
                break;
            default:
                return $partner;
                break;
        }
    }

    /**
     * To set partner transporter by each routes
     */
    public static function setPartnerTransporter($deliveryRoutes)
    {
        $transporter = null;
        $provinceId = $deliveryRoutes->packages->destination_regency->province_id;

        if (is_null($deliveryRoutes)) {
            return null;
        } else {
            $warehouse = self::checkWarehouse($deliveryRoutes);

            if ($deliveryRoutes->regency_destination_1 === 0) {
                $partner = DB::table('transport_routes')->where('province_id', $provinceId)->where('warehouse', $warehouse)->first();
                $transporter = self::getSelectedTransporter($deliveryRoutes, $partner);
            } else {
                $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', $warehouse)->first();

                if ($partner->note) {
                    $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', $warehouse)->get();
                }

                $listTransporter = [];
                if ($partner instanceof SupportCollection) {
                    foreach ($partner as $p) {
                        $partnerTransporter = self::getSelectedTransporter($deliveryRoutes, $p);
                        array_push($listTransporter, $partnerTransporter);
                    }
                    $transporter = $listTransporter;
                } else {
                    $transporter = self::getSelectedTransporter($deliveryRoutes, $partner);
                }
            }
        }
        return $transporter;
    }

    public static function getSelectedTransporter($deliveryRoutes, $partner)
    {
        switch (true) {
            case is_null($deliveryRoutes->reach_destination_1_at):
                return $partner->code_mtak_1;
                break;
            case is_null($deliveryRoutes->reach_destination_2_at):
                return $partner->code_mtak_2;
                break;
            case is_null($deliveryRoutes->reach_destination_3_at):
                return $partner->code_mtak_3;
                break;
            default:
                return null;
                break;
        }
    }

    /**
     * List of warehouse on routes map
     */
    public static function listWarehouse(): array
    {
        return [
            self::WAREHOUSE_AMBON,
            self::WAREHOUSE_BANDUNG,
            self::WAREHOUSE_BANJARMASIN,
            self::WAREHOUSE_MAKASSAR,
            self::WAREHOUSE_MATARAM,
            self::WAREHOUSE_NAROGONG,
            self::WAREHOUSE_PEKANBARU,
            self::WAREHOUSE_PONTIANAK,
            self::WAREHOUSE_SEMARANG,
            self::WAREHOUSE_SURABAYA,
            self::WAREHOUSE_TEGAL
        ];
    }

    public static function getWarehouseNearby($partner): array|null
    {
        switch (true) {
            case in_array($partner->geo_regency_id, self::jabodetabek()):
                $code = self::WAREHOUSE_NAROGONG;
                break;
            case in_array($partner->geo_regency_id, self::semarang()):
                $code = self::WAREHOUSE_SEMARANG;
                break;
            case in_array($partner->geo_regency_id, self::tegal()):
                $code = self::WAREHOUSE_TEGAL;
                break;
            case in_array($partner->geo_regency_id, self::makassar()):
                $code = self::WAREHOUSE_MAKASSAR;
                break;
            default:
                $code = null;
                break;
        }

        $warehouseOrigin =  Partner::query()->whereIn('code', $code)->get()->pluck('code')->toArray();
        return $warehouseOrigin ? $warehouseOrigin : null;
    }

    public static function checkWarehouse($deliveryRoutes)
    {
        switch (true) {
            case in_array($deliveryRoutes->originWarehouse->code, self::WAREHOUSE_NAROGONG):
                $warehouse = 'NAROGONG';
                break;
            case in_array($deliveryRoutes->originWarehouse->code, self::WAREHOUSE_PONTIANAK):
                $warehouse = 'PONTIANAK';
                break;
            case in_array($deliveryRoutes->originWarehouse->code, self::WAREHOUSE_BANDUNG):
                $warehouse = 'BANDUNG';
                break;
            case in_array($deliveryRoutes->originWarehouse->code, self::WAREHOUSE_AMBON):
                $warehouse = 'AMBON';
                break;
            case in_array($deliveryRoutes->originWarehouse->code, self::WAREHOUSE_PEKANBARU):
                $warehouse = 'PEKANBARU';
                break;
            case in_array($deliveryRoutes->originWarehouse->code, self::WAREHOUSE_SEMARANG):
                $warehouse = 'SEMARANG';
                break;
            case in_array($deliveryRoutes->originWarehouse->code, self::WAREHOUSE_SURABAYA):
                $warehouse = 'SURABAYA';
                break;
            case in_array($deliveryRoutes->originWarehouse->code, self::WAREHOUSE_TEGAL):
                $warehouse = 'TEGAL';
                break;
            case in_array($deliveryRoutes->originWarehouse->code, self::WAREHOUSE_MATARAM):
                $warehouse = 'MATARAM';
                break;
            case in_array($deliveryRoutes->originWarehouse->code, self::WAREHOUSE_MAKASSAR):
                $warehouse = 'MAKASSAR';
                break;
            case in_array($deliveryRoutes->originWarehouse->code, self::WAREHOUSE_BANJARMASIN):
                $warehouse = 'BANJARMASIN';
                break;
            default:
                $warehouse = null;
                break;
        }
        return $warehouse;
    }

    public static function jabodetabek(): array
    {
        return [
            58, 59, 60, 61, 62, 98, 77, 95, 36, 39, 40, 76, 94
        ];
    }

    public static function semarang(): array
    {
        return [
            123, 133
        ];
    }

    public static function tegal(): array
    {
        return [
            126, 135
        ];
    }

    public static function makassar(): array
    {
        return [
            393
        ];
    }


    public static function checkDestinationCityTransit($firstPackage, $packages): bool
    {
        $firstCount = null;
        $transits = [];

        if ($firstPackage->deliveryRoutes) {
            $firstCount = $firstPackage->deliveryRoutes->transit_count;
            switch (true) {
                case $firstCount === 3:
                    $destinationCity =  $firstPackage->deliveryRoutes->regency_dooring_id;
                    break;
                case $firstCount === 2:
                    $destinationCity =  $firstPackage->deliveryRoutes->regency_destination_3;
                    break;
                case $firstCount === 1:
                    $destinationCity =  $firstPackage->deliveryRoutes->regency_destination_2;
                    break;
                default:
                    $destinationCity = $firstPackage->deliveryRoutes->regency_destination_1;
                    break;
            }

            foreach ($packages as $package) {
                if ($package->deliveryRoutes) {
                    $transitCount = $package->deliveryRoutes->transit_count;
                    switch (true) {
                        case $transitCount === 3:
                            $destinationTransit =  $package->deliveryRoutes->regency_dooring_id;
                            break;
                        case $transitCount === 2:
                            $destinationTransit =  $package->deliveryRoutes->regency_destination_3;
                            break;
                        case $transitCount === 1:
                            $destinationTransit =  $package->deliveryRoutes->regency_destination_2;
                            break;
                        default:
                            $destinationTransit = $package->deliveryRoutes->regency_destination_1;
                            break;
                    }
                } else {
                    $destinationTransit = 0;
                }

                if ($destinationCity === $destinationTransit) {
                    $transit = 1;
                } else {
                    $transit = 0;
                }
                array_push($transits, $transit);
            }
        } else {
            return false;
        }

        if (!in_array(0, $transits)) {
            return true;
        } else {
            return false;
        }
    }
}
