<?php

namespace App\Http\Controllers\Api\Partner\Warehouse;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Master\PartnerResource;
use App\Http\Resources\Api\Delivery\DeliveryResource;
use App\Jobs\Deliveries\Actions\CreateNewManifest;
use App\Models\Partners\Partner;
use App\Supports\Repositories\PartnerRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ManifestController extends Controller
{
    public function index(Request $request, PartnerRepository $repository): JsonResponse
    {
        $query = $repository->queries()->getDeliveriesQuery();

        return $this->jsonSuccess(DeliveryResource::collection($query->paginate($request->input('per_page'))));
    }

    public function partner(Request $request, PartnerRepository $repository): JsonResponse
    {
        $query = Partner::query()->where('id', '!=', $repository->getPartner()->id);

        $query->when(
            $request->input('type'),
            fn(Builder $builder, $type) => $builder->whereIn('type', Arr::wrap($type)),
            fn(Builder $builder, $type) => $builder->whereIn('type', [
                Partner::TYPE_BUSINESS,
                Partner::TYPE_SPACE,
                Partner::TYPE_POOL
            ])
        );

        return $this->jsonSuccess(PartnerResource::collection($query->paginate($request->input('per_page'))));
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
}
