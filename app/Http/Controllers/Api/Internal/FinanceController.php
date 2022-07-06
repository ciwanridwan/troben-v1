<?php

namespace App\Http\Controllers\Api\Internal;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Internal\Finance\ListResource;
use App\Http\Resources\Api\Internal\Finance\DetailResource;
use App\Http\Resources\Api\Internal\Finance\OverviewResource;
use App\Http\Resources\Api\Internal\Finance\CountAmountResource;
use App\Http\Resources\Api\Internal\Finance\CountDisbursmentResource;
use App\Http\Resources\Api\Internal\Finance\FindByPartnerResource as FinanceFindByPartnerResource;
use App\Models\Partners\Partner;
use App\Models\Payments\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Builder;

class FinanceController extends Controller
{
    private const STATUS_APPROVE = 'approve';
    private const STATUS_REQUEST = 'request';
    private const STATUS_LIST = [
        self::STATUS_APPROVE,
        self::STATUS_REQUEST,
    ];

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @var Builder
     */
    protected Builder $query;

    /**Todo list disbursment */
    public function list(): JsonResponse
    {
        $result = Withdrawal::query();
        return $this->jsonSuccess(ListResource::collection($result->paginate(request('per_page', 10))));
    }
    /**End todo */

    /**Todo detail disbursment */
    public function detail(Withdrawal $withdrawal): JsonResponse
    {
        $result = Withdrawal::where('id', $withdrawal->id)->first();
        return $this->jsonSuccess(DetailResource::collection($result));
    }
    /**End Todo */

    /**Todo Count Request Disbursment */
    public function countDisbursment(Request $request)
    {
        return $this->jsonSuccess(new CountDisbursmentResource($request));
    }

    public function countAmountDisbursment(Request $request)
    {
        return $this->jsonSuccess(new CountAmountResource($request));
    }
    /**End Todo */

    public function overview(Request $request): JsonResponse
    {
        $result = [
            'mitra_count' => mt_rand(1, 10),
            'request_count' => mt_rand(11, 99) * 100000,
        ];

        return $this->jsonSuccess(new OverviewResource($result));
    }

    // Todo Find
    public function findByPartner(Request $request): JsonResponse
    {
        $this->attributes = $request->validate([
            'partner_id' => ['required'],
        ]);

        $partners = Partner::where('id', $this->attributes['partner_id'])->first();

        return $this->jsonSuccess(new FinanceFindByPartnerResource($partners));
    }

    public function findByStatus(Request $request): JsonResponse
    {
        $this->attributes = $request->validate([
            'status' => ['required'],
        ]);

        return $this->jsonSuccess(new FinanceFindByPartnerResource());
    }

    public function findByDate(Request $request): JsonResponse
    {
        $this->attributes = $request->validate([
            'date' => ['required'],
        ]);

        return $this->jsonSuccess(new FinanceFindByPartnerResource());
    }
    // End Todo
}
