<?php

namespace App\Actions\Deliveries;

use App\Models\Deliveries\Delivery;
use App\Models\Deliveries\DeliveryRoute;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Route
{
    // list of warehouse
    public const WAREHOUSE_NAROGONG = ['MPW-JKT-01'];

    public const WAREHOUSE_PONTIANAK = ['MB-PNK-01'];

    public const WAREHOUSE_BANDUNG = ['MB-BDG-01', 'MB-BDG-02', 'MB-BDG-03'];

    public const WAREHOUSE_PEKANBARU = ['MB-PKU-02'];

    public const WAREHOUSE_SEMARANG = ['MB-SMG-03', 'MB-SMG-02'];

    public const WAREHOUSE_SURABAYA = ['MPW-BJM-01', 'MB-SUB-01'];

    public const WAREHOUSE_TEGAL = ['MB-SLW-01'];

    public const WAREHOUSE_BANJARMASIN = ['MB-BDJ-03', 'MB-BDJ-02'];

    public const WAREHOUSE_MAKASSAR = ['MB-UPG-01', 'MB-UPG-05', 'MB-UPG-02', 'MB-UPG-03'];

    public const WAREHOUSE_UPG_MAKASSAR = ['MB-UPG-05'];

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
                if (!is_null($warehouse)) {
                    $checkRegency = self::checkRegency($warehouse);
                    if ($checkRegency) {
                        $regencyId = self::getFirstPartnerRegency($warehouse);
                    } else {
                        $regencyId = $warehouse instanceof SupportCollection ? $warehouse[0]->regency_id : $warehouse->regency_id;
                    }

                    if ($regencyId !== $q->destination_regency_id) {
                        $regencyId = $warehouse instanceof SupportCollection ? $warehouse[0]->regency_id : $warehouse->regency_id;
                    }

                    switch (true) {
                        case $warehouse instanceof SupportCollection:
                            $dooringPartner = self::getDooringPartner($warehouse[0]->code_dooring);
                            $nextDestination = self::getNextDestination($warehouse->toArray());
                            break;
                        default:
                            $dooringPartner = self::getDooringPartner($warehouse->code_dooring);
                            $nextDestination = self::getNextDestination($warehouse);
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
                }
            });

            $partnerByRoutes = [];
            foreach ($packages as $package) {
                if (!is_null($package->deliveryRoutes)) {
                    $partnerByRoute = self::setPartners($package->deliveryRoutes);
                    array_push($partnerByRoutes, $partnerByRoute);
                }
            }
            if (!empty($partnerByRoutes)) {
                $partnerCode = $partnerByRoutes;
            } else {
                $partnerCode = null;
            }
        } else {
            // set hardcode to exceptional condition in banjarmasin partner
            if ($partner->code === 'MB-BJM-02') {
                $partnerCode = array('MB-BDJ-02');
            } else {
                $partnerCode = self::getWarehouseNearby($partner);
            }
        }

        return $partnerCode;
    }

    /**
     * convert hash and get packages.
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

    /** To determine package can generate route or not.
     */
    public static function checkPackages($hash): int
    {
        $setPartner = 0; // default receipt without routing and old receipt
        $packagesId = [];
        $variants = [];

        for ($i = 0; $i < count($hash['package_hash']); $i++) {
            $packageId = Package::hashToId($hash['package_hash'][$i]);
            array_push($packagesId, $packageId);
        }

        $packages = Package::query()->whereIn('id', $packagesId)->get();
        foreach ($packages as $key => $value) {
            $route = $value->deliveryRoutes;
            if (!is_null($route)) { // new receipt with existing routes
                $setPartner = 1;
            }

            if ($value->deliveries->count() <= 2 && is_null($route)) { // new receipt
                $setPartner = 2;
            }

            if ($value->deliveries->count() > 2 && is_null($route)) { // condition old receipt
                $setPartner = 3;
            }

            array_push($variants, $setPartner);
        }

        switch (true) {
            case in_array(1, $variants):
                $type = 1;
                break;
            case in_array(2, $variants):
                $type = 2;
                break;
            case in_array(3, $variants):
                $type = 3;
                break;
            default:
                $type = 0;
                break;
        }

        return $type;
    }

    /**
     * Get warehouse partner for a depedency delivery routes.
     */
    public static function getWarehousePartner($partnerCode, $package)
    {
        $districtId = $package->destination_district_id;
        $regencyId = $package->destination_regency_id;
        $provinceId = $package->destination_regency->province_id;

        // check base on each warehouse, has match or not
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

        // check by regency
        $partner = DB::table('transport_routes')->where('warehouse', $warehouse)->where('regency_id', $regencyId)->first();

        // check by province id if by regency doest not exists
        if (is_null($partner)) {
            $partner = DB::table('transport_routes')->where('warehouse', $warehouse)->where('regency_id', 0)->where('province_id', $provinceId)->first();
        }
        
        // check if route get two destination MTAK
        if (!is_null($partner) && (is_null($partner->note) || $partner->note !== '')) {
            // get by regency
            $partner = DB::table('transport_routes')->where('warehouse', $warehouse)->where('regency_id', $regencyId)->get();

            // check if by regency is not, so switch get by province
            if ($partner->isEmpty()) {
                $partner = DB::table('transport_routes')->where('warehouse', $warehouse)->where('regency_id', 0)->where('province_id', $provinceId)->get();
            }
        }

        // check if route available direct to district
        if ($partner instanceof Partner) {
            if (!is_null($partner) && $partner->district_id !== 0) {
                $partner = DB::table('transport_routes')->where('warehouse', $warehouse)->where('regency_id', $regencyId)->where('district_id', $districtId)->first();
                
                // if get by district is null
                if (is_null($partner)) {
                    $partner = DB::table('transport_routes')->where('warehouse', $warehouse)->where('regency_id', $regencyId)->get();
                }
            }
        }

        // return with check if single or multiple routes
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
        $secondDestination = null;
        $thirdDestination = null;

        if (is_array($warehouse)) {
            $code = collect($warehouse)->map(function ($q) {
                return [
                    'mtak_2' => $q->code_mtak_2_dest,
                    'mtak_3' => $q->code_mtak_3_dest
                ];
            })->toArray();
            if (array_key_exists("0", $code)) {
                $secondDestination = $partner->where('code', $code[0]['mtak_2'])->first();
            }
            if (array_key_exists("1", $code)) {
                $thirdDestination = Partner::query()->where('code', $code[1]['mtak_3'])->first();
            }
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
     * Get dooring partner.
     */
    public static function getDooringPartner($code): Model|null
    {
        $partner = Partner::query()->where('code', $code)->first();
        return $partner ? $partner : null;
    }

    /**
     * Set partner to show in list.
     */
    public static function setPartners($deliveryRoutes)
    {
        $provinceId = $deliveryRoutes->packages ? $deliveryRoutes->packages->destination_regency->province_id : 0;
        $regencyId = $deliveryRoutes->regency_destination_1;
        $partner = null;

        $warehouse = self::checkWarehouse($deliveryRoutes);

        if ($deliveryRoutes->regency_destination_1 === 0) {
            $partner = DB::table('transport_routes')->where('province_id', $provinceId)->where('warehouse', $warehouse)->first();
        } else {
            $partner = DB::table('transport_routes')->where('regency_id', $regencyId)->where('warehouse', $warehouse)->first();
        }

        if (is_null($partner)) {
            return null;
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
     * To set partner transporter by each routes.
     */
    public static function setPartnerTransporter($deliveryRoutes)
    {
        $transporter = null;

        if (is_null($deliveryRoutes)) {
            return null;
        } else {
            $provinceId = $deliveryRoutes->packages->destination_regency->province_id;
            $warehouse = self::checkWarehouse($deliveryRoutes);

            if ($deliveryRoutes->regency_destination_1 === 0) {
                $countPartner = DB::table('transport_routes')->where('province_id', $provinceId)->where('warehouse', $warehouse)->count();
                if ($countPartner === 1) {
                    $partner = DB::table('transport_routes')->where('province_id', $provinceId)->where('warehouse', $warehouse)->first();
                    $transporter = self::getSelectedTransporter($deliveryRoutes, $partner);
                } else {
                    $partner = DB::table('transport_routes')->where('province_id', $provinceId)->where('warehouse', $warehouse)->get();
                    $listTransporter = [];
                    foreach ($partner as $p) {
                        $partnerTransporter = self::getSelectedTransporter($deliveryRoutes, $p);
                        array_push($listTransporter, $partnerTransporter);
                    }
                    $transporter = $listTransporter;
                }
            } else {
                $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', $warehouse)->first();

                if (!is_null($partner) && $partner->note) {
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

    /**
     * Select one partner base by each condition.
     */
    public static function getSelectedTransporter($deliveryRoutes, $partner)
    {
        if (is_null($partner)) {
            return null;
        }

        try {
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
        } catch (\Exception $e) {
            report($e);
            Log::info('errrouting', ['d' => $deliveryRoutes, 'p' => $partner]);
            return null;
        }
    }

    /**
     * List of warehouse on routes map.
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
                # turn off function to temporary
                // case in_array($partner->geo_regency_id, self::makassar()):
                //     $code = self::WAREHOUSE_UPG_MAKASSAR;
                //     break;
            default:
                $code = null;
                break;
        }

        $warehouseOrigin =  Partner::query()->whereIn('code', $code)->get()->pluck('code')->toArray();
        return $warehouseOrigin ? $warehouseOrigin : null;
    }

    public static function checkWarehouse($deliveryRoutes): string|null
    {
        if ($deliveryRoutes instanceof Delivery) {
            $code = $deliveryRoutes->partner ? $deliveryRoutes->partner->code : null;
        } else {
            $code = $deliveryRoutes->originWarehouse ? $deliveryRoutes->originWarehouse->code : null;
        }

        switch (true) {
            case in_array($code, self::WAREHOUSE_NAROGONG):
                $warehouse = 'NAROGONG';
                break;
            case in_array($code, self::WAREHOUSE_PONTIANAK):
                $warehouse = 'PONTIANAK';
                break;
            case in_array($code, self::WAREHOUSE_BANDUNG):
                $warehouse = 'BANDUNG';
                break;
            case in_array($code, self::WAREHOUSE_AMBON):
                $warehouse = 'AMBON';
                break;
            case in_array($code, self::WAREHOUSE_PEKANBARU):
                $warehouse = 'PEKANBARU';
                break;
            case in_array($code, self::WAREHOUSE_SEMARANG):
                $warehouse = 'SEMARANG';
                break;
            case in_array($code, self::WAREHOUSE_SURABAYA):
                $warehouse = 'SURABAYA';
                break;
            case in_array($code, self::WAREHOUSE_TEGAL):
                $warehouse = 'TEGAL';
                break;
            case in_array($code, self::WAREHOUSE_MATARAM):
                $warehouse = 'MATARAM';
                break;
            case in_array($code, self::WAREHOUSE_MAKASSAR):
                $warehouse = 'MAKASSAR';
                break;
            case in_array($code, self::WAREHOUSE_BANJARMASIN):
                $warehouse = 'BANJARMASIN';
                break;
            default:
                $warehouse = null;
                break;
        }
        return $warehouse;
    }

    /**
     * List id regency of jabodetabek
     */
    public static function jabodetabek(): array
    {
        return [
            58, 59, 60, 61, 62, 98, 77, 95, 36, 39, 40, 76, 94
        ];
    }

    /**
     * List id regency partner on makassar
     */
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

    public static function getFirstPartnerRegency($warehouse): int
    {
        switch (true) {
            case $warehouse instanceof SupportCollection:
                $partner = Partner::query()->whereIn('code', $warehouse->pluck('code_mtak_1_dest')->toArray())->first();
                if (is_null($partner)) {
                    $partner = Partner::query()->whereIn('code', $warehouse->pluck('code_mtak_1')->toArray())->first();
                }
                $regencyId = $partner->geo_regency_id;
                break;
            default:
                $code = null;
                if ($warehouse->code_mtak_1_dest === '') {
                    $code = $warehouse->code_mtak_1;
                } else {
                    $code = $warehouse->code_mtak_1_dest;
                }
                $partner = Partner::query()->where('code', $code)->first();
                $regencyId = $partner->geo_regency_id;
                break;
        }
        return $regencyId;
    }

    /**
     * Check regency id from partner and transport route.
     */
    public static function checkRegency($warehouse): bool
    {
        $fromPartner = true;
        switch ($warehouse) {
            case $warehouse instanceof SupportCollection:
                foreach ($warehouse as $w) {
                    if ($w->regency_id === 0) {
                        $fromPartner = false;
                    }
                }
                break;
            default:
                if ($warehouse->regency_id === 0) {
                    $fromPartner = false;
                }
                break;
        }
        return $fromPartner;
    }


    /**
     * Check receipt if match with transport routes records
     *
     */
    public static function checkDooring($package, $delivery, $type)
    {
        $isDooring = false;
        $warehouse = self::checkWarehouse($delivery);
        $regencyId = $package->destination_regency_id;
        $provinceId = $package->destination_regency->province_id;

        $route = DB::table('transport_routes')
            ->where('warehouse', $warehouse)
            ->where('regency_id', $regencyId)
            ->first();
        // if regency not found, fallback to province level
        if (is_null($route)) {
            $route = DB::table('transport_routes')
            ->where(function ($q) use ($warehouse, $provinceId) {
                $q->where('warehouse', $warehouse);
                $q->where('regency_id', 0);
                $q->where('province_id', $provinceId);
            })->first();
        }

        $firstCase = !is_null($route) && empty($route->code_mtak_1_dest);
        $secondCase = (!is_null($route) && !empty($route->code_mtak_1_dest)) && ($route->code_mtak_2_dest === $delivery->partner->code);
        $thirdCase = (!is_null($route) && !empty($route->code_mtak_1_dest)) && (!is_null($route) && !empty($route->code_mtak_2_dest));
        $lastCase = ($thirdCase && empty($route->code_mtak_3_dest) && ($thirdCase && $route->code_dooring === $delivery->partner->code));

        if ($type === 'dooring') {
            switch (true) {
                case $firstCase:
                    $isDooring = true;
                    break;
                case $secondCase:
                    $isDooring = true;
                    break;
                case $lastCase:
                    $isDooring = true;
                    break;
                default:
                    $isDooring = false;
                    break;
            }
        } else {
            if ($firstCase) {
                $isDooring = true;
            } else {
                $isDooring = false;
            }
        }

        return $isDooring;
    }

    /**
     * Check partner dooring, transporter or business
     */
    public static function checkVendorDooring($deliveryRoutes)
    {
        $check = false;

        $partnerType = $deliveryRoutes->partnerDoorings->type;
        if ($partnerType === Partner::TYPE_TRANSPORTER) {
            $check = true;
        }

        return $check;
    }
}
