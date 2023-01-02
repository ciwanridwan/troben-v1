<?php

namespace App\Actions\Deliveries;

use App\Models\Partners\Partner;

class Route
{
    // list of warehouse
    public const WAREHOUSE_NAROGONG = ['MPW-JKT-01'];

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

    /**
     * Get id from warehouse narogong partner
     * @return array $id
     */
    public static function getNarogongPartner(): array
    {
        $id = Partner::query()->whereIn('code', self::WAREHOUSE_NAROGONG)->get()->pluck('id')->toArray();

        return $id;
    }

}