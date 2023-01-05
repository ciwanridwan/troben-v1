<?php

namespace App\Actions\Deliveries;

use App\Jobs\Deliveries\Actions\CreateDeliveryRoute;
use App\Models\Code;
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

    public static function generate($partner, $codes)
    {
        $check = false;
        foreach (self::listWarehouse() as $key => $value) {
            if (in_array($partner->code, $value)) {
                $check = true;
            }
        }

        if ($check) {
            $packages = self::getPackages($codes);
            $packages->each(function ($q) use ($partner) {
                $warehouse = self::getWarehousePartner($partner->code, $q->destination_regency_id);
                dd($warehouse);
                $dooringPartner = self::getDooringPartner($warehouse->code_dooring);

                DeliveryRoute::create([
                    'package_id' => $q->id,
                    'regency_origin_id' => $partner->geo_regency_id,
                    'origin_warehouse_id' => $partner->id,
                    'regency_destination_1' => $warehouse->regency_id,
                    'regency_dooring_id' => $dooringPartner->geo_regency_id,
                    'partner_dooring_id' => $dooringPartner->id
                ]);
            });
        }
    }

    public static function getPackages($codes): Collection
    {
        $packageId = Code::query()->where('codeable_type', Package::class)->whereIn('content', $codes['code'])->get()->pluck('codeable_id')->toArray();

        $packages = Package::query()->whereIn('id', $packageId)->get();
        return $packages;
    }

    public static function getWarehousePartner($partnerCode, $regencyId)
    {
        switch (true) {
            case in_array($partnerCode, self::WAREHOUSE_NAROGONG):
                $warehouse = DB::table('transport_routes')->where('warehouse', 'NAROGONG')->where('regency_id', $regencyId)->first();
                return $warehouse;
                break;
            case in_array($partnerCode, self::WAREHOUSE_AMBON):
                $warehouse = DB::table('transport_routes')->where('warehouse', 'AMBON')->where('regency_id', $regencyId)->first();
                return $warehouse;
                break;
            case in_array($partnerCode, self::WAREHOUSE_BANDUNG):
                $warehouse = DB::table('transport_routes')->where('warehouse', 'BANDUNG')->where('regency_id', $regencyId)->first();
                return $warehouse;
                break;
            case in_array($partnerCode, self::WAREHOUSE_BANJARMASIN):
                $warehouse = DB::table('transport_routes')->where('warehouse', 'BANJARMASIN')->where('regency_id', $regencyId)->first();
                return $warehouse;
                break;
            case in_array($partnerCode, self::WAREHOUSE_MAKASSAR):
                $warehouse = DB::table('transport_routes')->where('warehouse', 'MAKASSAR')->where('regency_id', $regencyId)->first();
                return $warehouse;
                break;
            case in_array($partnerCode, self::WAREHOUSE_MATARAM):
                $warehouse = DB::table('transport_routes')->where('warehouse', 'MATARAM')->where('regency_id', $regencyId)->first();
                return $warehouse;
                break;
            case in_array($partnerCode, self::WAREHOUSE_PEKANBARU):
                $warehouse = DB::table('transport_routes')->where('warehouse', 'PEKANBARU')->where('regency_id', $regencyId)->first();
                return $warehouse;
                break;
            case in_array($partnerCode, self::WAREHOUSE_PONTIANAK):
                $warehouse = DB::table('transport_routes')->where('warehouse', 'PONTIANAK')->where('regency_id', $regencyId)->first();
                return $warehouse;
                break;
            case in_array($partnerCode, self::WAREHOUSE_SEMARANG):
                $warehouse = DB::table('transport_routes')->where('warehouse', 'SEMARANG')->where('regency_id', $regencyId)->first();
                return $warehouse;
                break;
            case in_array($partnerCode, self::WAREHOUSE_SURABAYA):
                $warehouse = DB::table('transport_routes')->where('warehouse', 'SURABAYA')->where('regency_id', $regencyId)->first();
                return $warehouse;
                break;
            case in_array($partnerCode, self::WAREHOUSE_TEGAL):
                $warehouse = DB::table('transport_routes')->where('warehouse', 'TEGAL')->where('regency_id', $regencyId)->first();
                return $warehouse;
                break;
            default:
                // todo
                break;
        }
    }

    public static function getDooringPartner($code): Model
    {
        $partner = Partner::query()->where('code', $code)->first();

        return $partner;
    }

    public static function setPartners($deliveryRoutes)
    {
        // if (is_null($deliveryRoutes->reach_destination_1_at) && in_array($deliveryRoutes->originWarehouse->code, self::WAREHOUSE_NAROGONG)) {
        //     $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', 'NAROGONG')->first();
        //     return $partner->code_mtak_1_dest;
        // }
        switch (true) {
            case in_array($deliveryRoutes->originWarehouse->code, self::WAREHOUSE_NAROGONG):
                if (is_null($deliveryRoutes->reach_destination_1_at)) {
                    $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', 'NAROGONG')->first();
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

    public static function setPartnerTransporter($deliveryRoutes)
    {
        // if (is_null($deliveryRoutes->reach_destination_1_at) && in_array($deliveryRoutes->originWarehouse->code, self::WAREHOUSE_NAROGONG)) {
        //     $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', 'NAROGONG')->first();
        //     return $partner->code_mtak_1;
        // }

        switch (true) {
            case in_array($deliveryRoutes->originWarehouse->code, self::WAREHOUSE_NAROGONG):
                if (is_null($deliveryRoutes->reach_destination_1_at)) {
                    $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', 'NAROGONG')->first();
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
}
