<?php

namespace App\Http\Controllers\Api\Partner\Cashier;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Partner\Cashier\OrderInvoiceResource;
use App\Models\Packages\Package;
use App\Supports\Repositories\PartnerRepository;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\Api\Order\Dashboard\DetailResource;

class OrderController extends Controller
{
    /**
     * @var Builder
     */
    protected Builder $query;

    /**
     * To get list orders, between get invoice orders and default orders
     */
    public function index(Request $request, PartnerRepository $partnerRepository): JsonResponse
    {
        $request->validate(
            [
                'q' => ['nullable', 'string'], // to search receipt code
                'status' => ['nullable', 'string', 'in:estimated']
            ]
        );

        $this->query = $partnerRepository->queries()->getPackagesQuery()->with(['code', 'multiDestination']);

        if (!is_null($request->q)) {
            $this->query->whereHas('code', function ($q) use ($request) {
                $q->where('content', 'ilike', '%'.$request->q.'%');
            });
        }

        if (!is_null($request->status) && $request->status === Package::STATUS_ESTIMATED) {
            $this->query->where('status', Package::STATUS_ESTIMATED);
        }

        return $this->jsonSuccess(OrderInvoiceResource::collection( $this->query->paginate(request('per_page', 10))));
    }
}
