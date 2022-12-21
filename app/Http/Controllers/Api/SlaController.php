<?php

namespace App\Http\Controllers\Api;

use App\Actions\Core\SlaLevel;
use App\Http\Controllers\Controller;
use App\Http\Response;
use App\Models\Partners\Balance\History;
use App\Models\Partners\Partner;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SlaController extends Controller
{
    /** Set alert level of SLA */
    public function setAlert()
    {
        SlaLevel::doSlaSetter();

        $message = ['message' => 'Called SLA Setter'];
        Log::info('Alert Level Two And Tree Just Running Of SLA');

        return (new Response(Response::RC_SUCCESS, $message))->json();
    }

    /**
     * Set penalty to partner when it late
     */
    public function setPenaltyDeliveries()
    {
        $q = "UPDATE partner_delivery_performances t
        SET status = 10,
            updated_at = NOW()
        WHERE 1=1
            AND level = 3
            AND status = 1
            AND reached_at IS NULL
            AND deadline < NOW()
            and not exists (
                select 1
                from partner_delivery_performances
                WHERE 1=1
                AND level = 3
                AND status = 10
                AND reached_at is null
                and deadline < now()
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

        $result = DB::statement($q);
        return $result;
    }
}
