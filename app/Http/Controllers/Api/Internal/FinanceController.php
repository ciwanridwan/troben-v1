<?php

namespace App\Http\Controllers\Api\Internal;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Internal\Finance\ListResource;
use App\Http\Resources\Api\Internal\Finance\DetailResource;
use App\Http\Resources\Api\Internal\Finance\OverviewResource;
use App\Http\Resources\Api\Internal\Finance\CountAmountResource;
use App\Http\Resources\Api\Internal\Finance\CountDisbursmentResource;
use App\Http\Resources\Api\Internal\Finance\FindByPartnerResource as FinanceFindByPartnerResource;
use App\Http\Resources\Api\Package\PackageResource;
use App\Http\Response;
use App\Models\Deliveries\Deliverable;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Item;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use App\Models\Payments\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

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

    private Package $package;
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
        $delivery = Delivery::has('packages')->where('partner_id', $result->partner_id);
        return $this->jsonSuccess(DetailResource::collection($delivery->paginate(request('per_page', 10))));
    }
    /**End Todo */

    /**Todo Submit Approved Disbursment */
    public function approve(Withdrawal $withdrawal): JsonResponse
    {
        $result = Withdrawal::where('id', $withdrawal->id)->first();
        $delivery = Delivery::has('packages')->where('partner_id', $result->partner_id);
        return $this->jsonSuccess(new DetailResource($delivery));
    }
    /**End todo */

    /**Todo Count Request Disbursment */
    public function countDisbursment(Withdrawal $withdrawal)
    {
        return $this->jsonSuccess(new CountDisbursmentResource($withdrawal));
    }

    public function countAmountDisbursment(Withdrawal $withdrawal)
    {
        return $this->jsonSuccess(new CountAmountResource($withdrawal));
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

        $partners = Withdrawal::where('partner_id', $this->attributes['partner_id'])->orderByDesc('created_at')->get();
        if ($partners->isEmpty()) {
            return (new Response(Response::RC_DATA_NOT_FOUND))->json();
        } else {
            return $this->jsonSuccess(ListResource::collection($partners));
        }
    }

    public function findByStatus(Request $request): JsonResponse
    {
        $this->attributes = $request->validate([
            'status' => ['required'],
        ]);

        if ($this->attributes['status'] == "requested") {
            $disbursmentStatus = Withdrawal::where('status', $this->attributes['status'])->orderByDesc('created_at')->get();
            return $this->jsonSuccess(ListResource::collection($disbursmentStatus));

        } else if ($this->attributes['status'] == "approved") {
            $disbursmentStatus = Withdrawal::where('status', $this->attributes['status'])->orderByDesc('created_at')->get();

            if ($disbursmentStatus->isEmpty()) {
                return (new Response(Response::RC_DATA_NOT_FOUND))->json();
            }

            return $this->jsonSuccess(ListResource::collection($disbursmentStatus));
        } else {
            return (new Response(Response::RC_DATA_NOT_FOUND))->json();
        }
    }

    public function findByDate(Request $request): JsonResponse
    {
        $this->attributes = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        $date = Withdrawal::whereDate('created_at', $this->attributes['date'])->get();
        if ($date->isEmpty()) {
            return (new Response(Response::RC_DATA_NOT_FOUND))->json();
        } else {
            return $this->jsonSuccess(ListResource::collection($date));
        }
    }
    // End Todo
}
