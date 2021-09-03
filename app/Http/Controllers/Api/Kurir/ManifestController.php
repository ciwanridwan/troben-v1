<?php

namespace App\Http\Controllers\Api\Kurir;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Delivery\DeliveryResource;
use App\Http\Resources\Api\Delivery\WarehouseManifestResource;
use App\Jobs\Deliveries\Actions\CreateNewManifest;
use App\Models\Deliveries\Delivery;
use App\Supports\Repositories\PartnerRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ManifestController extends Controller
{
    public function index(Request $request, PartnerRepository $repository): JsonResponse
    {
        $partner = $repository->getPartner();
        $query = $repository->queries()->getDeliveriesQuery();
        $request->whenHas('arrival', function (bool $value) use ($query, $partner) {
            if ($value) {
                $query->where('partner_id', $partner->id);
            }
        });
        $request->whenHas('departure', function (bool $value) use ($query, $partner) {
            if ($value) {
                $query->where('origin_partner_id', $partner->id);
            }
        });

        $query->with('partner', 'packages.items');

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
            'packages.code',
            'driver',
            'transporter',
        )));
    }
}
