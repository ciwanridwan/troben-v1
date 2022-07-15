<?php

namespace App\Http\Controllers\Api\Internal;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Internal\Finance\ListResource;
use App\Http\Resources\Api\Internal\Finance\OverviewResource;
use App\Http\Resources\Api\Internal\Finance\CountAmountResource;
use App\Http\Resources\Api\Internal\Finance\CountDisbursmentResource;
use App\Http\Response;
use App\Models\Partners\Balance\DisbursmentHistory;
use App\Models\Partners\Partner;
use App\Models\Payments\Withdrawal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
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
        $result = Withdrawal::orderBy('created_at', 'desc')->paginate(10);
        return $this->jsonSuccess(ListResource::collection($result));
    }
    /**End todo */

    /**Todo detail disbursment */
    public function detail(Withdrawal $withdrawal): JsonResponse
    {
        $result = Withdrawal::where('id', $withdrawal->id)->first();
        $query = $this->detailDisbursment($result);
        $packages = collect(DB::select($query));

        $approveds = $this->getApprovedDisbursment($packages->unique('receipt')->pluck('receipt')->values()->toArray());
        $approveds = collect(DB::select($approveds));

        $packages = $packages->map(function($r) use ($approveds) {
            $r->approved = 'pending';
            $check = $approveds->where('receipt', $r->receipt)->first();
            if ($check) {
                $r->approved = 'approved';
                $r->commission_discount = $check->amount;
            }

            return $r;
        });

        $data = $this->paginate($packages);

        return $this->jsonResponse($data);
    }
    /**End Todo */

    /**Todo Submit Approved Disbursment */
    public function approve(Withdrawal $withdrawal, Request $request): JsonResponse
    {
        $receipt = (array) $request->get('receipt');
        if (count($receipt) == 0) {
            return (new Response(Response::RC_BAD_REQUEST))->json();
        }

        $disbursment = Withdrawal::where('id', $withdrawal->id)->first();
        $query = $this->detailDisbursment($disbursment);
        $packages = collect(DB::select($query));

        $getReceipt = $packages->whereIn('receipt', $receipt)->map(function ($r) {
            $r->commission_discount = ceil($r->commission_discount);
            return $r;
        })->values();

        if ($getReceipt->isNotEmpty()) {
            $getReceipt->each(function ($r) use ($disbursment) {
                $disbursHistory = new DisbursmentHistory();
                $disbursHistory->disbursment_id = $disbursment->id;
                $disbursHistory->receipt = $r->receipt;
                $disbursHistory->amount = $r->commission_discount;
                $disbursHistory->save();
            });

            $commission_discount = $getReceipt->sum('commission_discount');
            $calculate = $disbursment->first_balance - $commission_discount;
            $disbursment->first_balance = $calculate;

            if ($disbursment->first_balance == $calculate) {
                $disbursment->amount = $commission_discount;
                $disbursment->status = Withdrawal::STATUS_APPROVED;
                $disbursment->action_by = Auth::id();
                $disbursment->action_at = Carbon::now();
                $disbursment->save();
            } else {
                return (new Response(Response::RC_BAD_REQUEST))->json();
            }

            return (new Response(Response::RC_UPDATED, $disbursment))->json();
        } else {
            return (new Response(Response::RC_DATA_NOT_FOUND))->json();
        }
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

    public function findByReceipt(Request $request, Withdrawal $withdrawal): JsonResponse
    {
        $this->attributes = $request->validate([
            'receipt' => ['required'],
        ]);

        $result = Withdrawal::where('id', $withdrawal->id)->first();
        $query = $this->detailDisbursment($result);
        $packages = collect(DB::select($query));
        $receipt = $packages->where('receipt', $this->attributes['receipt'])->first();
        if ($receipt->isEmpty()) {
            return (new Response(Response::RC_DATA_NOT_FOUND))->json();
        } else {
            $data = array($receipt);
            return $this->jsonResponse($data);
        }
    }
    // End Todo

    /** Custom Paginate */
    public function paginate($items, $perPage = 15, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }
    /**End Paginate */

    /**Query for get spesific data from many tables */
    private function detailDisbursment($request)
    {
        $q =
            "SELECT p.total_amount total_payment, c.content receipt, (p.total_amount * 0.3) as commission_discount

        FROM deliveries d
        LEFT JOIN (
            SELECT *
            FROM deliverables
            WHERE deliverable_type = 'App\Models\Packages\Package'
        ) dd ON d.id = dd.delivery_id
        LEFT JOIN packages p ON dd.deliverable_id = p.id
        LEFT JOIN (
            SELECT *
            FROM codes
            WHERE codeable_type = 'App\Models\Packages\Package'
        ) c ON p.id = c.codeable_id
        WHERE 1=1 AND
        d.partner_id IN (
            SELECT partner_id
            FROM partner_balance_disbursement
            WHERE partner_id = $request->partner_id
        )
        AND dd.delivery_id IS NOT NULL";

        return $q;
    }
    /**End query */

    /**Query for get approved disbursement */
    private function getApprovedDisbursment($receipts) {
        $q = "SELECT * FROM disbursment_histories WHERE receipt IN (%s)";
        $q = sprintf($q, implode(',', $receipts));

        return $q;
    }
    /**End query */
}
