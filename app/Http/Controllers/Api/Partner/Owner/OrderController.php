<?php

namespace App\Http\Controllers\Api\Partner\Owner;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use App\Supports\Repositories\PartnerRepository;
use App\Http\Resources\Api\Package\PackageResource;

class OrderController extends Controller
{
    public function index(Request $request, PartnerRepository $repository): JsonResponse
    {
        $query = $repository->queries()->getPackagesQuery();

        $query->when($request->input('status'), fn (Builder $builder, $status) => $builder->whereIn('status', Arr::wrap($status)));

        $query->when($request->input('delivery_type'), fn (Builder $builder, $deliveryType) => $builder
            ->whereHas('deliveries', fn (Builder $builder) => $builder
                ->whereIn('type', Arr::wrap($deliveryType))));

        $query->with(['estimator', 'packager']);

        return $this->jsonSuccess(PackageResource::collection($query->paginate($request->input('per_page'))));
    }
}
