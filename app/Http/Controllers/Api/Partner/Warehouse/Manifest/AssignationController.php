<?php

namespace App\Http\Controllers\Api\Partner\Warehouse\Manifest;

use App\Jobs\Deliveries\Actions\RequestPartnerToDelivery;
use App\Models\Partners\Partner;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Deliveries\Delivery;
use App\Http\Controllers\Controller;
use App\Models\Deliveries\Deliverable;
use App\Models\Partners\Pivot\UserablePivot;
use App\Jobs\Deliveries\Actions\AssignDriverToDelivery;
use App\Jobs\Deliveries\Actions\AssignPartnerToDelivery;
use App\Jobs\Deliveries\Actions\ProcessFromCodeToDelivery;
use Illuminate\Support\Facades\DB;

/**
 * Class AssignationController.
 */
class AssignationController extends Controller
{
    /**
     * @param Delivery $delivery
     * @param UserablePivot $userablePivot
     * @return JsonResponse
     */
    public function driver(Delivery $delivery, UserablePivot $userablePivot): JsonResponse
    {
        $method = 'partner';
        $job = new AssignDriverToDelivery($delivery, $userablePivot, $method);

        $this->dispatchNow($job);

        return $this->jsonSuccess();
    }

    /**
     * @param Delivery $delivery
     * @return JsonResponse
     */
    public function requestPartner(Delivery $delivery): JsonResponse
    {
        $job = new RequestPartnerToDelivery($delivery);

        $this->dispatchNow($job);

        return $this->jsonSuccess();
    }

    /**
     * @param Delivery $delivery
     * @param Partner $partner
     * @return JsonResponse
     */
    public function partner(Delivery $delivery, Partner $partner): JsonResponse
    {
        $job = new AssignPartnerToDelivery($delivery, $partner);

        $this->dispatchNow($job);

        return $this->jsonSuccess();
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Deliveries\Delivery $delivery
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function package(Request $request, Delivery $delivery): JsonResponse
    {
        $inputs = array_merge($request->only(['code']));
        if (count($inputs)) {
            // code package
            $q = "select content  from codes c where
            codeable_type = 'App\Models\Packages\Package' and
            codeable_id  in (

            select package_id  from package_items pi2 where id in (

                select codeable_id from codes where codeable_type ='App\Models\Packages\Item' and content in (%s) order by codeable_id desc
            )
            group by package_id
            )";
            $idPackages = collect(DB::select(sprintf($q, implode(',', $inputs))))->pluck('content')->toArray();

            foreach ($idPackages as $idp) {
                $inputs[] = $idp;
            }
        }
        $inputs = array_unique($inputs);
        $job = new ProcessFromCodeToDelivery($delivery, $inputs, [
            'status' => Deliverable::STATUS_PREPARED_BY_ORIGIN_WAREHOUSE,
            'role' => UserablePivot::ROLE_WAREHOUSE
        ]);

        $this->dispatchNow($job);

        return $this->jsonSuccess();
    }
}
