<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SlaController extends Controller
{
    /** Set fine (denda) to income partner */
    public function incomePenalty()
    {
        $data = collect(DB::select($this->commisionOfSla()));

    }

    /** Set alert level of SLA */
    public function setAlert()
    {
        $this->levelTwoDeliveries();
        $this->levelTreeDeliveries();
        $this->levelTwoPackages();
        $this->levelTreePackages();
        $message = ['message' => 'Running Alert'];
        Log::info('Alert Level Two And Tree Just Running Of SLA');

        return (new Response(Response::RC_SUCCESS, $message))->json();
    }

    /**
     * Set alert level 2 for deliveries (manifest)
     */
    public function levelTwoDeliveries()
    {
        $q = "UPDATE partner_delivery_performances t
        SET level = 2,
            deadline = deadline + interval '24' hour,
            updated_at = NOW()
        WHERE 1=1
            AND level = 1
            AND status = 1
            AND reached_at IS NULL
            AND deadline < NOW()
            and not exists (
                select 1
                from partner_delivery_performances
                WHERE 1=1
                AND level = 2
                AND status = 1
                AND delivery_id = t.delivery_id
                AND partner_id  = t.partner_id
            )";

        $result = DB::statement($q);
        return $result;
    }

    /**
     * Set alert level 3 for deliveries
     */
    public function levelTreeDeliveries()
    {
        $q = "UPDATE partner_delivery_performances t
        SET level  = 3,
            deadline = deadline + interval '24' hour,
            updated_at = NOW()
        WHERE 1=1
            AND level = 2
            AND status = 1
            AND reached_at IS NULL
            AND deadline < NOW()
            and not exists (
                select 1
                from partner_delivery_performances
                WHERE 1=1
                AND level = 3
                AND status = 1
                AND delivery_id = t.delivery_id
                AND partner_id  = t.partner_id
            )";

        $result = DB::statement($q);
        return $result;
    }

    /** Set alert level 2 for packages */
    public function levelTwoPackages()
    {
        $q = "UPDATE partner_package_performances t
        SET level = 2,
            deadline = deadline + interval '24' hour,
            updated_at = NOW()
        WHERE 1=1
            AND level = 1
            AND status = 1
            AND reached_at IS NULL
            AND deadline < NOW()
            and not exists (
                select 1
                from partner_package_performances
                WHERE 1=1
                AND level = 2
                AND status = 1
                AND package_id = t.package_id
                AND partner_id  = t.partner_id
            )";

        $result = DB::statement($q);
        return $result;
    }

    /** Set alert level 3 for packages */
    public function levelTreePackages()
    {
        $q = "UPDATE partner_package_performances t
        SET level = 3,
            deadline = deadline + interval '24' hour,
            updated_at = NOW()
        WHERE 1=1
            AND level = 2
            AND status = 1
            AND reached_at IS NULL
            AND deadline < NOW()
            and not exists (
                select 1
                from partner_package_performances
                WHERE 1=1
                AND level = 3
                AND status = 1
                AND package_id = t.package_id
                AND partner_id  = t.partner_id
            )";

        $result = DB::statement($q);
        return $result;
    }

    /**
     * Query for get commision from each delivery_id
     */
    public function commisionOfSla()
    {
        $q = "SELECT pdp.partner_id, pdp.delivery_id,
        dd.deliverable_id as package_id,
        pp.amount as service_fee,
        p.type
            from partner_delivery_performances pdp
            left join (select * from deliverables dd where dd.deliverable_type = 'App\Models\Packages\Package') dd on dd.delivery_id = pdp.delivery_id
            left join (
                select pp.amount, pp.package_id from package_prices pp where pp.type = 'service' and
                pp.description = 'service' or pp.description = 'express' or pp.description = 'kubikasi'
                ) pp on dd.deliverable_id = pp.package_id
            left join ( select * from partners p) p on p.id = pdp.partner_id
                where pdp.level = 3
                and pdp.reached_at is null
                and pdp.deadline < now()
                and pdp.status = 10
                and dd.delivery_id is not null";

        return $q;
    }
}
