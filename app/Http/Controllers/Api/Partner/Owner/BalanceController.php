<?php

namespace App\Http\Controllers\Api\Partner\Owner;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Partner\Owner\Balance\DetailResource;
use App\Http\Resources\Api\Partner\Owner\Balance\ReportResource;
use App\Http\Resources\Api\Partner\Owner\Balance\SummaryResource;
use App\Supports\Repositories\PartnerBalanceReportRepository;
use App\Supports\Repositories\PartnerRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BalanceController extends Controller
{
    /** @var Builder $query */
    protected Builder $query;

    /** @var array $attributes */
    protected array $attributes;

    /**
     * @param Request $request
     * @param PartnerRepository $repository
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function index(Request $request, PartnerRepository $repository): JsonResponse
    {
        $inputs = array_merge($request->all(), [
            'group' => ['package_code','package_id','package_created_at'],
            'partner_id' => $repository->getPartner()->id,
            'is_package_created' => true
        ]);

        $this->query = (new PartnerBalanceReportRepository($inputs))->getQuery();

        $this->query->with('balanceHistories', fn ($q) => $q->where('partner_id',$repository->getPartner()->id));

        return $this->jsonSuccess(ReportResource::collection($this->query->paginate($request->input('per_page',10))));
    }

    /**
     * @param PartnerRepository $repository
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function summary(PartnerRepository $repository): JsonResponse
    {
        $this->query = $repository->queries()->getPartnerBalanceReportQuery();

        return $this->jsonSuccess(SummaryResource::make($this->query->get()));
    }

    /**
     * @param Request $request
     * @param PartnerRepository $repository
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function detail(Request $request, PartnerRepository $repository): JsonResponse
    {
        $inputs = array_merge($request->all(), [
            'partner_id' => $repository->getPartner()->id,
        ]);

        $this->query = (new PartnerBalanceReportRepository($inputs))->getQuery();

        return $this->jsonSuccess(DetailResource::make($this->query->paginate($request->input('per_page',10))));
    }
}
