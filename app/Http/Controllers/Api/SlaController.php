<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class SlaController extends Controller
{
    public function setLevel()
    {
        $deliveries = DB::statement($this->querySlaDeliveries());
        // dd($deliveries);
        DB::statement($this->querySlaPackages());
        $message = ['message' => 'Running Alert Level'];

        return (new Response(Response::RC_SUCCESS, $message))->json();
    }

    public function querySlaDeliveries()
    {
        $q = "UPDATE partner_delivery_performances t
        SET 'level' = 2,
            deadline = deadline + interval '24' hour,
            updated_at = NOW()
        WHERE 1=1
            AND 'level' = 1
            AND status = 1
            AND reached_at IS NULL
            AND deadline < NOW()
            and not exists (
                select 1
                from partner_delivery_performances
                WHERE 1=1
                AND 'level' = 2
                AND status = 1
                AND delivery_id = t.delivery_id
                AND partner_id  = t.partner_id
            )";

            return $q;
    }


    public function querySlaPackages()
    {
        $q = "UPDATE partner_package_perfomances t
        SET 'level' = 2,
            deadline = deadline + interval '24' hour,
            updated_at = NOW()
        WHERE 1=1
            AND 'level' = 1
            AND status = 1
            AND reached_at IS NULL
            AND deadline < NOW()
            and not exists (
                select 1
                from partner_package_perfomances
                WHERE 1=1
                AND 'level' = 2
                AND status = 1
                AND delivery_id = t.delivery_id
                AND partner_id  = t.partner_id
            )";

            return $q;
    }
}
