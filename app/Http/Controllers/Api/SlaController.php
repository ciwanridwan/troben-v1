<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Response;
use App\Models\Partners\Partner;
use App\Models\Partners\Performances\Delivery as PerformanceDelivery;
use App\Models\Partners\Performances\PerformanceModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SlaController extends Controller
{
    /** Set fine (denda) to income partner */
    public function incomePenalty()
    {
        $levelTree = PerformanceDelivery::query()->where('level', 3)->whereNull('reached_at')->where('status', PerformanceModel::STATUS_ON_PROCESS)->where('deadline', '<', Carbon::now())->get()->pluck('partner_id')->toArray();

        $partner = Partner::query()->whereIn('id', $levelTree)->get();
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
}
