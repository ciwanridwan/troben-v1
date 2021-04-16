<?php

namespace App\Http\Controllers\Api\Partner\Warehouse;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Deliveries\Delivery;
use App\Http\Controllers\Controller;
use App\Supports\Repositories\PartnerRepository;
use App\Jobs\Deliveries\Actions\CreateNewManifest;
use App\Http\Resources\Api\Delivery\DeliveryResource;
use App\Http\Resources\Api\Delivery\WarehouseManifestResource;

class ManifestController extends Controller
{
    public function index(Request $request, PartnerRepository $repository): JsonResponse
    {
        $query = $repository->queries()->getDeliveriesQuery();

        $query->with('partner', 'packages');

        return $this->jsonSuccess(DeliveryResource::collection($query->paginate($request->input('per_page'))));
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Supports\Repositories\PartnerRepository $repository
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request, PartnerRepository $repository): JsonResponse
    {
        $job = new CreateNewManifest($repository->getPartner(), $request->all());

        $this->dispatchNow($job);

        return $this->jsonSuccess();
    }

    public function show(Delivery $delivery): JsonResponse
    {
        return $this->jsonSuccess(WarehouseManifestResource::make($delivery->load(
            'item_codes',
            'code',
            'partner',
            'packages',
            'driver',
            'transporter',
        )));
    }
}
