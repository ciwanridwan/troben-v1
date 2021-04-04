<?php

namespace App\Http\Controllers\Api\Partner\Owner;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Supports\Repositories\PartnerRepository;
use App\Http\Resources\Api\Package\PackageResource;
use Illuminate\Support\Arr;

class OrderController extends Controller
{
    public function index(Request $request, PartnerRepository $repository): JsonResponse
    {
        $query = $repository->queries()->getPackagesQuery();

        $query->when($request->input('status'), fn(Builder $builder, $status) => $builder->whereIn('status', Arr::wrap($status)));

        return $this->jsonSuccess(PackageResource::collection($query->paginate($request->input('per_page'))));
    }
}
