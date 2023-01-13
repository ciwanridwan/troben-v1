<?php

namespace App\Actions\Deliveries;

use App\Models\Deliveries\DeliveryRoute;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
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
                $dooringPartner = self::getDooringPartner($warehouse->code_dooring);
                $secondDestination = self::getSecondDestination($warehouse);
                $thirdDestination = self::getThirdDestination($warehouse);

                $checkPackages = DeliveryRoute::query()->where('package_id', $q->id)->first();
                if (is_null($checkPackages)) {
                    DeliveryRoute::create([
                        'package_id' => $q->id,
                        'regency_origin_id' => $partner->geo_regency_id,
                        'origin_warehouse_id' => $partner->id,
                        'regency_destination_1' => $warehouse->regency_id,
                        'regency_destination_2' => $secondDestination,
                        'regency_destination_3' => $thirdDestination,
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

    /**
     * Get warehouse partner for a depedency delivery routes
     */
    public static function getWarehousePartner($partnerCode, $package)
    {
        $warehouse = null;
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
                // todo
                break;
        }

        $partner = DB::table('transport_routes')->where('warehouse', $warehouse)->where('regency_id', $regencyId)->orWhere(function ($q) use ($warehouse, $provinceId) {
            $q->where('warehouse', $warehouse);
            $q->where('regency_id', 0);
            $q->where('province_id', $provinceId);
        })->first();

        return $partner ? $partner : null;

        // switch (true) {
        //     case in_array($partnerCode, self::WAREHOUSE_NAROGONG):
        //         $warehouse = DB::table('transport_routes')->where('warehouse', 'NAROGONG')->where('regency_id', $regencyId)->orWhere(function ($q) use ($provinceId) {
        //             $q->where('warehouse', 'NAROGONG');
        //             $q->where('regency_id', 0);
        //             $q->where('province_id', $provinceId);
        //         })->first();
        //         return $warehouse;
        //         break;
        //     case in_array($partnerCode, self::WAREHOUSE_AMBON):
        //         $warehouse = DB::table('transport_routes')->where('warehouse', 'AMBON')->where('regency_id', $regencyId)->orWhere(function ($q) use ($provinceId) {
        //             $q->where('warehouse', 'AMBON');
        //             $q->where('regency_id', 0);
        //             $q->where('province_id', $provinceId);
        //         })->first();
        //         return $warehouse;
        //         break;
        //     case in_array($partnerCode, self::WAREHOUSE_BANDUNG):
        //         $warehouse = DB::table('transport_routes')->where('warehouse', 'BANDUNG')->where('regency_id', $regencyId)->orWhere(function ($q) use ($provinceId) {
        //             $q->where('warehouse', 'BANDUNG');
        //             $q->where('regency_id', 0);
        //             $q->where('province_id', $provinceId);
        //         })->first();
        //         return $warehouse;
        //         break;
        //     case in_array($partnerCode, self::WAREHOUSE_BANJARMASIN):
        //         $warehouse = DB::table('transport_routes')->where('warehouse', 'BANJARMASIN')->where('regency_id', $regencyId)->orWhere(function ($q) use ($provinceId) {
        //             $q->where('warehouse', 'BANJARMASIN');
        //             $q->where('regency_id', 0);
        //             $q->where('province_id', $provinceId);
        //         })->first();
        //         return $warehouse;
        //         break;
        //     case in_array($partnerCode, self::WAREHOUSE_MAKASSAR):
        //         $warehouse = DB::table('transport_routes')->where('warehouse', 'MAKASSAR')->where('regency_id', $regencyId)->orWhere(function ($q) use ($provinceId) {
        //             $q->where('warehouse', 'MAKASSAR');
        //             $q->where('regency_id', 0);
        //             $q->where('province_id', $provinceId);
        //         })->first();
        //         return $warehouse;
        //         break;
        //     case in_array($partnerCode, self::WAREHOUSE_MATARAM):
        //         $warehouse = DB::table('transport_routes')->where('warehouse', 'MATARAM')->where('regency_id', $regencyId)->orWhere(function ($q) use ($provinceId) {
        //             $q->where('warehouse', 'MATARAM');
        //             $q->where('regency_id', 0);
        //             $q->where('province_id', $provinceId);
        //         })->first();
        //         return $warehouse;
        //         break;
        //     case in_array($partnerCode, self::WAREHOUSE_PEKANBARU):
        //         $warehouse = DB::table('transport_routes')->where('warehouse', 'PEKANBARU')->where('regency_id', $regencyId)->orWhere(function ($q) use ($provinceId) {
        //             $q->where('warehouse', 'PEKANBARU');
        //             $q->where('regency_id', 0);
        //             $q->where('province_id', $provinceId);
        //         })->first();
        //         return $warehouse;
        //         break;
        //     case in_array($partnerCode, self::WAREHOUSE_PONTIANAK):
        //         $warehouse = DB::table('transport_routes')->where('warehouse', 'PONTIANAK')->where('regency_id', $regencyId)->orWhere(function ($q) use ($provinceId) {
        //             $q->where('warehouse', 'PONTIANAK');
        //             $q->where('regency_id', 0);
        //             $q->where('province_id', $provinceId);
        //         })->first();
        //         return $warehouse;
        //         break;
        //     case in_array($partnerCode, self::WAREHOUSE_SEMARANG):
        //         $warehouse = DB::table('transport_routes')->where('warehouse', 'SEMARANG')->where('regency_id', $regencyId)->orWhere(function ($q) use ($provinceId) {
        //             $q->where('warehouse', 'SEMARANG');
        //             $q->where('regency_id', 0);
        //             $q->where('province_id', $provinceId);
        //         })->first();
        //         return $warehouse;
        //         break;
        //     case in_array($partnerCode, self::WAREHOUSE_SURABAYA):
        //         $warehouse = DB::table('transport_routes')->where('warehouse', 'SURABAYA')->where('regency_id', $regencyId)->orWhere(function ($q) use ($provinceId) {
        //             $q->where('warehouse', 'SURABAYA');
        //             $q->where('regency_id', 0);
        //             $q->where('province_id', $provinceId);
        //         })->first();
        //         return $warehouse;
        //         break;
        //     case in_array($partnerCode, self::WAREHOUSE_TEGAL):
        //         $warehouse = DB::table('transport_routes')->where('warehouse', 'TEGAL')->where('regency_id', $regencyId)->orWhere(function ($q) use ($provinceId) {
        //             $q->where('warehouse', 'TEGAL');
        //             $q->where('regency_id', 0);
        //             $q->where('province_id', $provinceId);
        //         })->first();
        //         return $warehouse;
        //         break;
        //     default:
        //         // todo
        //         break;
        // }
    }

    public static function getSecondDestination($warehouse): int|null
    {
        $partner = Partner::query()->where('code', $warehouse->code_mtak_2_dest)->first();

        return $partner ? $partner->geo_regency_id : null;
    }

    public static function getThirdDestination($warehouse): int|null
    {
        $partner = Partner::query()->where('code', $warehouse->code_mtak_3_dest)->first();

        return $partner ? $partner->geo_regency_id : null;
    }

    /**
     * Get dooring partner
     */
    public static function getDooringPartner($code): Model
    {
        $partner = Partner::query()->where('code', $code)->first();
        return $partner;
    }

    /**
     * Set partner to show in list
     */
    public static function setPartners($deliveryRoutes)
    {
        $provinceId = $deliveryRoutes->packages->destination_regency->province_id;
        $regencyId = $deliveryRoutes->regency_destination_1;
        $partner = null;

        switch (true) {
            case in_array($deliveryRoutes->originWarehouse->code, self::WAREHOUSE_NAROGONG):
                if (is_null($deliveryRoutes->reach_destination_1_at)) {
                    if ($deliveryRoutes->regency_destination_1 === 0) {
                        $partner = DB::table('transport_routes')->where('province_id', $provinceId)->where('warehouse', 'NAROGONG')->first();
                    } else {
                        $partner = DB::table('transport_routes')->where('regency_id', $regencyId)->where('warehouse', 'NAROGONG')->first();
                    }
                    return $partner->code_mtak_1_dest;
                } elseif (is_null($deliveryRoutes->reach_destination_2_at)) {
                    $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_2)->where('warehouse', 'NAROGONG')->first();
                    return $partner->code_mtak_2_dest;
                } elseif (is_null($deliveryRoutes->reach_destination_3_at)) {
                    $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_3)->where('warehouse', 'NAROGONG')->first();
                    return $partner->code_mtak_3_dest;
                } else {
                    break;
                }
                break;
            case in_array($deliveryRoutes->originWarehouse->code, self::WAREHOUSE_PONTIANAK):
                if (is_null($deliveryRoutes->reach_destination_1_at)) {
                    $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', 'PONTIANAK')->first();
                    return $partner->code_mtak_1_dest;
                } elseif (is_null($deliveryRoutes->reach_destination_2_at)) {
                    $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_2)->where('warehouse', 'PONTIANAK')->first();
                    return $partner->code_mtak_2_dest;
                } elseif (is_null($deliveryRoutes->reach_destination_3_at)) {
                    $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_3)->where('warehouse', 'PONTIANAK')->first();
                    return $partner->code_mtak_3_dest;
                } else {
                    break;
                }
                break;
            case in_array($deliveryRoutes->originWarehouse->code, self::WAREHOUSE_BANDUNG):
                if (is_null($deliveryRoutes->reach_destination_1_at)) {
                    $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', 'BANDUNG')->first();
                    return $partner->code_mtak_1_dest;
                } elseif (is_null($deliveryRoutes->reach_destination_2_at)) {
                    $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_2)->where('warehouse', 'BANDUNG')->first();
                    return $partner->code_mtak_2_dest;
                } elseif (is_null($deliveryRoutes->reach_destination_3_at)) {
                    $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_3)->where('warehouse', 'BANDUNG')->first();
                    return $partner->code_mtak_3_dest;
                } else {
                    break;
                }
                break;
            case in_array($deliveryRoutes->originWarehouse->code, self::WAREHOUSE_AMBON):
                if (is_null($deliveryRoutes->reach_destination_1_at)) {
                    $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', 'AMBON')->first();
                    return $partner->code_mtak_1_dest;
                } elseif (is_null($deliveryRoutes->reach_destination_2_at)) {
                    $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_2)->where('warehouse', 'AMBON')->first();
                    return $partner->code_mtak_2_dest;
                } elseif (is_null($deliveryRoutes->reach_destination_3_at)) {
                    $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_3)->where('warehouse', 'AMBON')->first();
                    return $partner->code_mtak_3_dest;
                } else {
                    break;
                }
                break;
            case in_array($deliveryRoutes->originWarehouse->code, self::WAREHOUSE_PEKANBARU):
                if (is_null($deliveryRoutes->reach_destination_1_at)) {
                    $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', 'PEKANBARU')->first();
                    return $partner->code_mtak_1_dest;
                } elseif (is_null($deliveryRoutes->reach_destination_2_at)) {
                    $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_2)->where('warehouse', 'PEKANBARU')->first();
                    return $partner->code_mtak_2_dest;
                } elseif (is_null($deliveryRoutes->reach_destination_3_at)) {
                    $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_3)->where('warehouse', 'PEKANBARU')->first();
                    return $partner->code_mtak_3_dest;
                } else {
                    break;
                }
                break;
            case in_array($deliveryRoutes->originWarehouse->code, self::WAREHOUSE_SEMARANG):
                if (is_null($deliveryRoutes->reach_destination_1_at)) {
                    $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', 'SEMARANG')->first();
                    return $partner->code_mtak_1_dest;
                } elseif (is_null($deliveryRoutes->reach_destination_2_at)) {
                    $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_2)->where('warehouse', 'SEMARANG')->first();
                    return $partner->code_mtak_2_dest;
                } elseif (is_null($deliveryRoutes->reach_destination_3_at)) {
                    $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_3)->where('warehouse', 'SEMARANG')->first();
                    return $partner->code_mtak_3_dest;
                } else {
                    break;
                }
                break;
            case in_array($deliveryRoutes->originWarehouse->code, self::WAREHOUSE_SURABAYA):
                if (is_null($deliveryRoutes->reach_destination_1_at)) {
                    $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', 'SURABAYA')->first();
                    return $partner->code_mtak_1_dest;
                } elseif (is_null($deliveryRoutes->reach_destination_2_at)) {
                    $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_2)->where('warehouse', 'SURABAYA')->first();
                    return $partner->code_mtak_2_dest;
                } elseif (is_null($deliveryRoutes->reach_destination_3_at)) {
                    $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_3)->where('warehouse', 'SURABAYA')->first();
                    return $partner->code_mtak_3_dest;
                } else {
                    break;
                }
                break;
            case in_array($deliveryRoutes->originWarehouse->code, self::WAREHOUSE_TEGAL):
                if (is_null($deliveryRoutes->reach_destination_1_at)) {
                    $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', 'TEGAL')->first();
                    return $partner->code_mtak_1_dest;
                } elseif (is_null($deliveryRoutes->reach_destination_2_at)) {
                    $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_2)->where('warehouse', 'TEGAL')->first();
                    return $partner->code_mtak_2_dest;
                } elseif (is_null($deliveryRoutes->reach_destination_3_at)) {
                    $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_3)->where('warehouse', 'TEGAL')->first();
                    return $partner->code_mtak_3_dest;
                } else {
                    break;
                }
                break;
            case in_array($deliveryRoutes->originWarehouse->code, self::WAREHOUSE_MATARAM):
                if (is_null($deliveryRoutes->reach_destination_1_at)) {
                    $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', 'MATARAM')->first();
                    return $partner->code_mtak_1_dest;
                } elseif (is_null($deliveryRoutes->reach_destination_2_at)) {
                    $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_2)->where('warehouse', 'MATARAM')->first();
                    return $partner->code_mtak_2_dest;
                } elseif (is_null($deliveryRoutes->reach_destination_3_at)) {
                    $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_3)->where('warehouse', 'MATARAM')->first();
                    return $partner->code_mtak_3_dest;
                } else {
                    break;
                }
                break;
            case in_array($deliveryRoutes->originWarehouse->code, self::WAREHOUSE_MAKASSAR):
                if (is_null($deliveryRoutes->reach_destination_1_at)) {
                    $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', 'MAKASSAR')->first();
                    return $partner->code_mtak_1_dest;
                } elseif (is_null($deliveryRoutes->reach_destination_2_at)) {
                    $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_2)->where('warehouse', 'MAKASSAR')->first();
                    return $partner->code_mtak_2_dest;
                } elseif (is_null($deliveryRoutes->reach_destination_3_at)) {
                    $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_3)->where('warehouse', 'MAKASSAR')->first();
                    return $partner->code_mtak_3_dest;
                } else {
                    break;
                }
                break;
            case in_array($deliveryRoutes->originWarehouse->code, self::WAREHOUSE_BANJARMASIN):
                if (is_null($deliveryRoutes->reach_destination_1_at)) {
                    $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', 'BANJARMASIN')->first();
                    return $partner->code_mtak_1_dest;
                } elseif (is_null($deliveryRoutes->reach_destination_2_at)) {
                    $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_2)->where('warehouse', 'BANJARMASIN')->first();
                    return $partner->code_mtak_2_dest;
                } elseif (is_null($deliveryRoutes->reach_destination_3_at)) {
                    $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_3)->where('warehouse', 'BANJARMASIN')->first();
                    return $partner->code_mtak_3_dest;
                } else {
                    break;
                }
                break;
            default:
                # code...
                break;
        }
    }

    /**
     * To set partner transporter by each routes
     */
    public static function setPartnerTransporter($deliveryRoutes)
    {
        $provinceId = $deliveryRoutes->packages->destination_regency->province_id;

        if (is_null($deliveryRoutes)) {
            return null;
        } else {
            switch (true) {
                case in_array($deliveryRoutes->originWarehouse->code, self::WAREHOUSE_NAROGONG):
                    if (is_null($deliveryRoutes->reach_destination_1_at)) {
                        if ($deliveryRoutes->regency_destination_1 === 0) {
                            $partner = DB::table('transport_routes')->where('province_id', $provinceId)->where('warehouse', 'NAROGONG')->first();
                        } else {
                            $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', 'NAROGONG')
                                ->first();
                        }
                        return $partner->code_mtak_1;
                    } elseif (is_null($deliveryRoutes->reach_destination_2_at)) {
                        $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', 'NAROGONG')->first();
                        return $partner->code_mtak_2;
                    } elseif (is_null($deliveryRoutes->reach_destination_3_at)) {
                        $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', 'NAROGONG')->first();
                        return $partner->code_mtak_3;
                    } else {
                        break;
                    }
                    break;
                case in_array($deliveryRoutes->originWarehouse->code, self::WAREHOUSE_BANDUNG):
                    if (is_null($deliveryRoutes->reach_destination_1_at)) {
                        $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', 'BANDUNG')->first();
                        return $partner->code_mtak_1;
                    } elseif (is_null($deliveryRoutes->reach_destination_2_at)) {
                        $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', 'BANDUNG')->first();
                        return $partner->code_mtak_2;
                    } elseif (is_null($deliveryRoutes->reach_destination_3_at)) {
                        $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', 'BANDUNG')->first();
                        return $partner->code_mtak_3;
                    } else {
                        break;
                    }
                    break;
                case in_array($deliveryRoutes->originWarehouse->code, self::WAREHOUSE_PONTIANAK):
                    if (is_null($deliveryRoutes->reach_destination_1_at)) {
                        $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', 'PONTIANAK')->first();
                        return $partner->code_mtak_1;
                    } elseif (is_null($deliveryRoutes->reach_destination_2_at)) {
                        $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', 'PONTIANAK')->first();
                        return $partner->code_mtak_2;
                    } elseif (is_null($deliveryRoutes->reach_destination_3_at)) {
                        $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', 'PONTIANAK')->first();
                        return $partner->code_mtak_3;
                    } else {
                        break;
                    }
                    break;
                case in_array($deliveryRoutes->originWarehouse->code, self::WAREHOUSE_PEKANBARU):
                    if (is_null($deliveryRoutes->reach_destination_1_at)) {
                        $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', 'PEKANBARU')->first();
                        return $partner->code_mtak_1;
                    } elseif (is_null($deliveryRoutes->reach_destination_2_at)) {
                        $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', 'PEKANBARU')->first();
                        return $partner->code_mtak_2;
                    } elseif (is_null($deliveryRoutes->reach_destination_3_at)) {
                        $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', 'PEKANBARU')->first();
                        return $partner->code_mtak_3;
                    } else {
                        break;
                    }
                    break;
                case in_array($deliveryRoutes->originWarehouse->code, self::WAREHOUSE_AMBON):
                    if (is_null($deliveryRoutes->reach_destination_1_at)) {
                        $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', 'AMBON')->first();
                        return $partner->code_mtak_1;
                    } elseif (is_null($deliveryRoutes->reach_destination_2_at)) {
                        $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', 'AMBON')->first();
                        return $partner->code_mtak_2;
                    } elseif (is_null($deliveryRoutes->reach_destination_3_at)) {
                        $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', 'AMBON')->first();
                        return $partner->code_mtak_3;
                    } else {
                        break;
                    }
                    break;
                case in_array($deliveryRoutes->originWarehouse->code, self::WAREHOUSE_SEMARANG):
                    if (is_null($deliveryRoutes->reach_destination_1_at)) {
                        $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', 'SEMARANG')->first();
                        return $partner->code_mtak_1;
                    } elseif (is_null($deliveryRoutes->reach_destination_2_at)) {
                        $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', 'SEMARANG')->first();
                        return $partner->code_mtak_2;
                    } elseif (is_null($deliveryRoutes->reach_destination_3_at)) {
                        $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', 'SEMARANG')->first();
                        return $partner->code_mtak_3;
                    } else {
                        break;
                    }
                    break;
                case in_array($deliveryRoutes->originWarehouse->code, self::WAREHOUSE_SURABAYA):
                    if (is_null($deliveryRoutes->reach_destination_1_at)) {
                        $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', 'SURABAYA')->first();
                        return $partner->code_mtak_1;
                    } elseif (is_null($deliveryRoutes->reach_destination_2_at)) {
                        $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', 'SURABAYA')->first();
                        return $partner->code_mtak_2;
                    } elseif (is_null($deliveryRoutes->reach_destination_3_at)) {
                        $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', 'SURABAYA')->first();
                        return $partner->code_mtak_3;
                    } else {
                        break;
                    }
                    break;
                case in_array($deliveryRoutes->originWarehouse->code, self::WAREHOUSE_TEGAL):
                    if (is_null($deliveryRoutes->reach_destination_1_at)) {
                        $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', 'TEGAL')->first();
                        return $partner->code_mtak_1;
                    } elseif (is_null($deliveryRoutes->reach_destination_2_at)) {
                        $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', 'TEGAL')->first();
                        return $partner->code_mtak_2;
                    } elseif (is_null($deliveryRoutes->reach_destination_3_at)) {
                        $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', 'TEGAL')->first();
                        return $partner->code_mtak_3;
                    } else {
                        break;
                    }
                    break;
                case in_array($deliveryRoutes->originWarehouse->code, self::WAREHOUSE_BANJARMASIN):
                    if (is_null($deliveryRoutes->reach_destination_1_at)) {
                        $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', 'BANJARMASIN')->first();
                        return $partner->code_mtak_1;
                    } elseif (is_null($deliveryRoutes->reach_destination_2_at)) {
                        $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', 'BANJARMASIN')->first();
                        return $partner->code_mtak_2;
                    } elseif (is_null($deliveryRoutes->reach_destination_3_at)) {
                        $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', 'BANJARMASIN')->first();
                        return $partner->code_mtak_3;
                    } else {
                        break;
                    }
                    break;
                case in_array($deliveryRoutes->originWarehouse->code, self::WAREHOUSE_MAKASSAR):
                    if (is_null($deliveryRoutes->reach_destination_1_at)) {
                        $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', 'MAKASSAR')->first();
                        return $partner->code_mtak_1;
                    } elseif (is_null($deliveryRoutes->reach_destination_2_at)) {
                        $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', 'MAKASSAR')->first();
                        return $partner->code_mtak_2;
                    } elseif (is_null($deliveryRoutes->reach_destination_3_at)) {
                        $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', 'MAKASSAR')->first();
                        return $partner->code_mtak_3;
                    } else {
                        break;
                    }
                    break;
                case in_array($deliveryRoutes->originWarehouse->code, self::WAREHOUSE_MATARAM):
                    if (is_null($deliveryRoutes->reach_destination_1_at)) {
                        $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', 'MATARAM')->first();
                        return $partner->code_mtak_1;
                    } elseif (is_null($deliveryRoutes->reach_destination_2_at)) {
                        $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', 'MATARAM')->first();
                        return $partner->code_mtak_2;
                    } elseif (is_null($deliveryRoutes->reach_destination_3_at)) {
                        $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', 'MATARAM')->first();
                        return $partner->code_mtak_3;
                    } else {
                        break;
                    }
                    break;
                default:
                    # code...
                    break;
            }
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

    public static function getWarehouseNearby($partner): array
    {
        switch (true) {
            case in_array($partner->geo_regency_id, self::jabodetabek()):
                $warehouseOrigin = Partner::query()->whereIn('code', self::WAREHOUSE_NAROGONG)->get()->pluck('code')->toArray();
                return $warehouseOrigin;
                break;

            case in_array($partner->geo_regency_id, self::semarang()):
                $warehouseOrigin = Partner::query()->whereIn('code', self::WAREHOUSE_SEMARANG)->get()->pluck('code')->toArray();
                return $warehouseOrigin;
                break;
            case in_array($partner->geo_regency_id, self::tegal()):
                $warehouseOrigin = Partner::query()->whereIn('code', self::WAREHOUSE_TEGAL)->get()->pluck('code')->toArray();
                return $warehouseOrigin;
                break;
            case in_array($partner->geo_regency_id, self::makassar()):
                $warehouseOrigin = Partner::query()->whereIn('code', self::WAREHOUSE_MAKASSAR)->get()->pluck('code')->toArray();
                return $warehouseOrigin;
                break;
            default:
                break;
        }
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
}
