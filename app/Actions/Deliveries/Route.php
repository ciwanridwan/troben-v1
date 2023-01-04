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
    public const WAREHOUSE_NAROGONG = ['MPW-JKT-01', 'MB-JKT-05'];

    public const WAREHOUSE_PONTIANAK = [];

    public const WAREHOUSE_BANDUNG = [];

    public const WAREHOUSE_PEKANBARU = [];

    public const WAREHOUSE_SEMARANG = [];

    public const WAREHOUSE_SURABAYA = [];

    public const WAREHOUSE_TEGAL = [];

    public const WAREHOUSE_BANJARMASIN = [];

    public const WAREHOUSE_MAKASSAR = [];

    public const WAREHOUSE_MATARAM = [];

    public const WAREHOUSE_AMBON = [];
    // end list

    public static function generate($partner, $codes)
    {
        $packages = self::getPackages($codes);
        $packages->each(function ($q) use ($partner) {
            $warehouse = self::getWarehousePartner($partner->code, $q->destination_regency_id);
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
        if (is_null($deliveryRoutes->reach_destination_1_at) && in_array($deliveryRoutes->originWarehouse->code, self::WAREHOUSE_NAROGONG)) {
            $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', 'NAROGONG')->first();
            return $partner->code_mtak_1_dest;
        }
    }

    public static function setPartnerTransporter($deliveryRoutes)
    {
        if (is_null($deliveryRoutes->reach_destination_1_at) && in_array($deliveryRoutes->originWarehouse->code, self::WAREHOUSE_NAROGONG)) {
            $partner = DB::table('transport_routes')->where('regency_id', $deliveryRoutes->regency_destination_1)->where('warehouse', 'NAROGONG')->first();
            return $partner->code_mtak_1;
        }
    }
}
