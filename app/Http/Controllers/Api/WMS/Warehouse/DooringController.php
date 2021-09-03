<?php

namespace App\Http\Controllers\Api\WMS\Warehouse;

use App\Http\Controllers\Controller;
use App\Jobs\Deliveries\Actions\CreateNewDooring;
use App\Supports\Repositories\PartnerRepository;

class DooringController extends Controller
{
    public function store(PartnerRepository $repository)
    {
        $job = new CreateNewDooring($repository->getPartner());

        $this->dispatchNow($job);

        return $this->jsonSuccess();
    }
}
