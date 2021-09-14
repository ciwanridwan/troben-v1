<?php

namespace App\Http\Controllers\Api\WMS\Warehouse;

use App\Http\Controllers\Controller;
use App\Jobs\Deliveries\Actions\CreateNewDooring;
use App\Jobs\Deliveries\Actions\ProcessFromCodeToDelivery;
use App\Models\Deliveries\Deliverable;
use App\Models\Partners\Pivot\UserablePivot;
use App\Supports\Repositories\PartnerRepository;
use Illuminate\Http\Request;

class DooringController extends Controller
{
    public function store(Request $request, PartnerRepository $repository)
    {
        $job = new CreateNewDooring($repository->getPartner());
        $this->dispatchNow($job);

        $job = new ProcessFromCodeToDelivery($job->delivery, array_merge($request->only(['code']), [
            'status' => Deliverable::STATUS_PREPARED_BY_ORIGIN_WAREHOUSE,
            'role' => UserablePivot::ROLE_WAREHOUSE
        ]));
        $this->dispatchNow($job);

        return $this->jsonSuccess();
    }
}
