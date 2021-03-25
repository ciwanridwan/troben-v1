<?php

namespace App\Http\Controllers\Api\Partner\Driver;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Supports\Repositories\PartnerRepository;
use App\Http\Resources\Api\Delivery\DeliveryResource;

class OrderController extends Controller
{
    public function index(Request $request, PartnerRepository $repository): JsonResponse
    {
        $query = $repository->queries()->getDeliveriesQuery();

        $query->with('packages');

        $query->orderByDesc('created_at');

        return $this->jsonSuccess(DeliveryResource::collection($query->paginate($request->input('per_page', 15))));
    }
}
