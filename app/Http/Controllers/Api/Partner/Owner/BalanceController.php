<?php

namespace App\Http\Controllers\Api\Partner\Owner;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Partner\Owner\Balance\HistoryResource;
use App\Http\Resources\Api\Partner\Owner\Balance\SummaryResource;
use App\Models\Partners\Partner;
use App\Supports\Repositories\PartnerRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BalanceController extends Controller
{
    /** @var Builder $query */
    protected Builder $query;

    /**
     * @param Request $request
     * @param PartnerRepository $repository
     * @return JsonResponse
     */
    public function index(Request $request, PartnerRepository $repository): JsonResponse
    {
        $this->query = $repository->queries()->getPartnerBalanceHistoryQuery();
        if ($request->input('type') !== 'all') {
            $this->query->when($request->input('type'), fn (Builder $builder, $type) => $builder->where('type', $type));
        }

        return $this->jsonSuccess(HistoryResource::collection($this->query->paginate($request->input('per_page', 10))));
    }

    /**
     * @param PartnerRepository $repository
     * @return JsonResponse
     */
    public function summary(PartnerRepository $repository): JsonResponse
    {
        $this->query = $repository->queries()->getPartnerBalanceHistoryQuery();

        return $this->jsonSuccess(SummaryResource::make($this->query->get()));
    }
}
