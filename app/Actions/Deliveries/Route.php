<?php

namespace App\Actions\Deliveries;

use App\Jobs\Deliveries\Actions\CreateDeliveryRoute;
use App\Models\Code;
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
        $warehouse = self::getWarehousePartner($partner->code);
        // dd($warehouse);
        $packages = self::getPackages($codes);
    }

    public static function getPackages($codes): Collection
    {
        $packageId = Code::query()->where('codeable_type', Package::class)->whereIn('content', $codes['code'])->get()->pluck('codeable_id')->toArray();

        $packages = Package::query()->whereIn('id', $packageId)->get();
        return $packages;
    }

    public static function getWarehousePartner($partnerCode)
    {
        switch (true) {
            case in_array($partnerCode, self::WAREHOUSE_NAROGONG):
                $warehouse = DB::table('transport_routes')->where('warehouse', 'NAROGONG')->get();
                return $warehouse;
                break;
            default:
                // todo
                break;
        }
    }
}