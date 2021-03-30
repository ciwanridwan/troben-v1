<?php

namespace App\Http\Controllers\Api\Partner\Warehouse;

use Illuminate\Http\Request;
use App\Models\Packages\Package;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Supports\Repositories\PartnerRepository;
use App\Http\Resources\Api\Package\PackageResource;
use App\Events\Packages\PackageEstimatedByWarehouse;

class OrderController extends Controller
{
    public function index(Request $request, PartnerRepository $repository): JsonResponse
    {
        $query = $repository->queries()->getPackagesQuery();

        return $this->jsonSuccess(PackageResource::collection($query->paginate($request->input('per_page', 15))));
    }

    public function estimated(Package $package): JsonResponse
    {
        event(new PackageEstimatedByWarehouse($package));

        return $this->jsonSuccess(PackageResource::make($package));
    }
}
