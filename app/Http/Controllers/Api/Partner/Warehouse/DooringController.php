<?php

namespace App\Http\Controllers\Api\Partner\Warehouse;

use App\Http\Controllers\Controller;
use App\Jobs\Deliveries\Actions\CreateNewDooring;
use App\Supports\Repositories\PartnerRepository;
use Illuminate\Http\Request;
use App\Jobs\Deliveries\Actions\V2\ProcessFromCodeToDelivery;
use App\Models\Deliveries\Deliverable;
use App\Models\Deliveries\Delivery;
use App\Models\Partners\Pivot\UserablePivot;
use Illuminate\Support\Facades\DB;

class DooringController extends Controller
{
    public function store(PartnerRepository $repository, Request $request)
    {
        $job = new CreateNewDooring($repository->getPartner(), $request->all());
        $this->dispatchNow($job);

        $this->insertPackagesToDelivery($request->all(), $job->delivery);

        return $this->jsonSuccess();
    }

     /** Insert packages to new delivery */
     public function insertPackagesToDelivery($request, Delivery $delivery): void
     {
         $inputs = array_merge($request);
         if (count($inputs['code'])) {
             // code package
             $q = "select content  from codes c where
             codeable_type = 'App\Models\Packages\Package' and
             codeable_id  in (

             select package_id  from package_items pi2 where id in (

                 select codeable_id from codes where codeable_type ='App\Models\Packages\Item' and content in ('%s') order by codeable_id desc
             )
             group by package_id
             )";
             $idPackages = collect(DB::select(sprintf($q, implode("','", $inputs['code']))))->pluck('content')->toArray();

             foreach ($idPackages as $idp) {
                 $inputs['code'][] = $idp;
             }
         }
         $inputs['code'] = array_unique($inputs['code']);

         $inputs['status'] = Deliverable::STATUS_PREPARED_BY_ORIGIN_WAREHOUSE;
         $inputs['role'] = UserablePivot::ROLE_WAREHOUSE;
         $job = new ProcessFromCodeToDelivery(
             $delivery,
             $inputs
         );

         $this->dispatchNow($job);
     }
}
