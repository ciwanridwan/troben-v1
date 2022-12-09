<?php

namespace App\Http\Controllers\Api\Internal;

use App\Exports\DisbursmentExport;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Internal\Finance\ListResource;
use App\Http\Resources\Api\Internal\Finance\CountAmountResource;
use App\Http\Resources\Api\Internal\Finance\CountDisbursmentResource;
use App\Http\Response;
use App\Models\Partners\Balance\DeliveryHistory;
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
use Illuminate\Support\Facades\Storage;

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

    /**List disbursment */
    public function list(): JsonResponse
    {
        $result = Withdrawal::whereHas('partner')->orderBy('created_at', 'desc')->get();
        return $this->jsonSuccess(ListResource::collection($result));
    }

    /** Count Request Disbursment */
    public function countDisbursment(Withdrawal $withdrawal)
    {
        return $this->jsonSuccess(new CountDisbursmentResource($withdrawal));
    }

    /** Count Total Request Disbursment */
    public function countAmountDisbursment(Withdrawal $withdrawal)
    {
        return $this->jsonSuccess(new CountAmountResource($withdrawal));
    }

    // Todo Find
    public function findByPartner(Request $request): JsonResponse
    {
        $this->attributes = $request->validate([
            'partner_id' => ['nullable'],
        ]);

        $partners = Withdrawal::where('partner_id', $this->attributes['partner_id'])->orderByDesc('created_at')->paginate(10);
        if ($partners->isEmpty()) {
            return (new Response(Response::RC_SUCCESS, []))->json();
        } else {
            return $this->jsonSuccess(ListResource::collection($partners));
        }
    }

    public function findByStatus(Request $request): JsonResponse
    {
        $this->attributes = $request->validate([
            'status' => ['nullable'],
        ]);

        if ($this->attributes['status'] == 'requested') {
            $disbursmentStatus = Withdrawal::where('status', $this->attributes['status'])->orderByDesc('created_at')->paginate(10);
            return $this->jsonSuccess(ListResource::collection($disbursmentStatus));
        } elseif ($this->attributes['status'] == 'approved') {
            $disbursmentStatus = Withdrawal::where('status', $this->attributes['status'])->orderByDesc('created_at')->paginate(10);

            if ($disbursmentStatus->isEmpty()) {
                return (new Response(Response::RC_SUCCESS, []))->json();
            }

            return $this->jsonSuccess(ListResource::collection($disbursmentStatus));
        } else {
            return (new Response(Response::RC_SUCCESS, []))->json();
        }
    }

    public function findByDate(Request $request): JsonResponse
    {
        $this->attributes = $request->validate([
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $date = Withdrawal::whereBetween('created_at', [$this->attributes['start_date'], $this->attributes['end_date']])->paginate(10);

        if ($date->isEmpty()) {
            return (new Response(Response::RC_SUCCESS, []))->json();
        } else {
            return $this->jsonSuccess(ListResource::collection($date));
        }
    }

    public function findByReceipt(Request $request, Withdrawal $withdrawal): JsonResponse
    {
        $this->attributes = $request->validate([
            'receipt' => ['nullable'],
        ]);

        $result = Withdrawal::where('id', $request->id)->first();
        if (is_null($result)) {
            return (new Response(Response::RC_SUCCESS, []))->json();
        }

        $query = $this->detailDisbursment($result);
        $packages = collect(DB::select($query));

        $receipt = $packages->where('receipt', $this->attributes['receipt'])->map(function ($r) {
            $r->total_payment = intval($r->total_payment);
            $r->total_accepted = intval($r->total_accepted);

            $disbursHistory = DisbursmentHistory::where('receipt', $this->attributes['receipt'])->first();
            if (is_null($disbursHistory)) {
                $r->approved = 'pending';
                return $r;
            } elseif ($disbursHistory->receipt == $r->receipt) {
                $r->approved = 'success';
                return $r;
            } else {
                $r->approved = 'pending';
                return $r;
            }
        })->first();

        if (is_null($receipt)) {
            return (new Response(Response::RC_SUCCESS, []))->json();
        } else {
            $data = [$receipt];
            return (new Response(Response::RC_SUCCESS, $data))->json();
        }
    }
    // End Todo

    /**Get list partner for findByPartner Function */
    public function listPartners()
    {
        $data = Partner::select('id', 'name', 'code')->get();
        return (new Response(Response::RC_SUCCESS, $data))->json();
    }

    /** Custom Paginate */
    public function paginate($items, $perPage = 15, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }

    public function reportReceipt(Request $request)
    {
        $request->validate([
            'start' => 'required|date_format:Y-m-d',
            'end' => 'required|date_format:Y-m-d',
        ]);

        $param = [
            'start' => $request->get('start', Carbon::now()->subMonth()->format('Y-m-d')),
            'end' => $request->get('end', Carbon::now()->format('Y-m-d')),
        ];
        $q = $this->reportReceiptQuery($param);
        $result = collect(DB::select($q));

        $filename = 'TB-Sales ' . date('Y-m-d H-i-s') . '.xls';
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Content-type: application/vnd-ms-excel');
        header('Cache-Control: max-age=0');

        return view('report.finance', compact('result'));
    }

    /**Add report excel for disbursment */
    public function export(Request $request)
    {
        $request->validate([
            'start' => 'nullable|date_format:Y-m-d',
            'end' => 'nullable|date_format:Y-m-d',
        ]);

        $param = [
            'start' => $request->get('start', Carbon::now()->subMonth()->format('Y-m-d')),
            'end' => $request->get('end', Carbon::now()->format('Y-m-d')),
        ];

        $result = $this->getQueryExports($param);

        $data = collect(DB::select($result))->toArray();

        return (new DisbursmentExport($data))->download('Disbursment-Histories.xlsx');
    }

    public static function detailDisbursment($request)
    {
        $q =
            "SELECT
            r.total_payment,
            r.receipt,
            (r.pickup_fee + r.packing_fee + r.insurance_fee + r.partner_fee + r.extra_charge - r.discount) as total_accepted
            from (
            SELECT
            p.total_amount total_payment,
            c.content receipt,
            coalesce(pp.amount, 0) as pickup_fee,
            coalesce(packing_fee, 0) as packing_fee,
            coalesce(insurance_fee, 0) as insurance_fee,
            coalesce(pp4.amount * 0.3, 0) as partner_fee,
            coalesce(pp5.amount, 0) as discount,
            case
                when weight > 99 then coalesce(pp4.amount * 0.05, 0)
                else 0
            end as extra_charge
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
                    left join (select pi2.package_id, sum(pi2.weight) as weight from package_items pi2 group by 1)
                    pi2 on pi2.package_id = c.codeable_id
                    left join (select pp.package_id, pp.amount from package_prices pp where type = 'delivery')
                    pp on pp.package_id = c.codeable_id
                    left join (select pp2.package_id, sum(pp2.amount) as packing_fee from package_prices pp2 where type = 'handling' group by 1)
                    pp2 on pp2.package_id = c.codeable_id
                    left join (select pp3.package_id, sum(pp3.amount) as insurance_fee from package_prices pp3 where type = 'insurance' group by 1)
                    pp3 on pp3.package_id = c.codeable_id
                    left join (select pp4.package_id, pp4.amount from package_prices pp4 where type = 'service')
                    pp4 on pp4.package_id = c.codeable_id
                    left join (select pp5.package_id, pp5.amount from package_prices pp5 where type = 'discount' and description = 'service')
                    pp5 on pp5.package_id = c.codeable_id
                    WHERE 1=1 AND
                    d.partner_id IN (
                    SELECT partner_id
                    FROM partner_balance_disbursement
                    WHERE partner_id = $request->partner_id
                    )
                    AND dd.delivery_id IS NOT null
                    ) r";
        return $q;
    }

    /**
     * Get a detail of disbursment.
     * @param $disbursment_id
     * */
    public function detail($disbursment_id)
    {
        $disbursment = Withdrawal::where('id', $disbursment_id)->first();

        if (is_null($disbursment)) {
            return (new Response(Response::RC_SUCCESS, []))->json();
        }

        $partner = $disbursment->partner()->first();

        switch ($partner->type) {
            case Partner::TYPE_TRANSPORTER:
                return $this->detailDisbursTransporter($partner->type, $disbursment);
                break;
            default:
                return $this->detailDisburs($disbursment);
                break;
        }
    }

    /** New Script Approve
     *  Approve some receipt or manifest code to disbursment.
     *  @helloirfanaditya
     *  @ryanda
     */
    public function approve(Withdrawal $withdrawal, Request $request): JsonResponse
    {
        $receipt = (array) $request->get('receipt');
        if (count($receipt) == 0) {
            return (new Response(Response::RC_BAD_REQUEST))->json();
        }

        $disbursment = Withdrawal::where('id', $request->id)->first();
        if (is_null($disbursment)) {
            return (new Response(Response::RC_SUCCESS, []))->json();
        }

        $partnerType = $disbursment->partner->type;
        switch ($partnerType) {
            case Partner::TYPE_TRANSPORTER:
                return $this->approveForDeliveries($disbursment, $receipt);
                break;
            default:
                return $this->approveForPackages($disbursment, $receipt);
                break;
        }
    }

    /**
     * Query for get reporting receipt of paid receipts.
     * @ciwanridwan
     */
    public function reportReceiptQuery($param)
    {
        $q = "SELECT
        receipt_code,
        origin_city,
        destination_province,
        destination_city,
        destination_district,
        destination_sub_district,
        zip_code,
        type_order,
        transporter_pickup_type,
        unloaded_at,
        origin_partner,
        nicepay_trx_id,
        nicepay_status,
        payment_verified_at,
        payment_request_at,
        total_weight,
        item_price,
        total_delivery_price,
        discount_delivery,
        extra_commission,
        commission_manual,
        total_commission,
        receipt_total_packing_price,
        receipt_insurance_price,
        receipt_pickup_price,
        receipt_total_amount
        FROM view_receipt_paid rp
        WHERE 1=1
        AND DATE(payment_verified_at) >= '%s'
        AND DATE(payment_verified_at) <= '%s'";

        $q = sprintf($q, $param['start'], $param['end']);

        return $q;
    }

    /**Query for get all disbursment with spesific data */
    private function getQueryExports($param)
    {
        $q =
            "SELECT
            r.partner_name,
            r.bank_name,
            r.bank_number,
            r.receipt,
            r.weight,
            r.pickup_fee,
            r.packing_fee,
            r.packing_bike_fee,
            r.insurance_fee,
            r.partner_fee,
            r.discount_fee,
            r.commision,
            (r.pickup_fee+r.packing_fee+r.packing_bike_fee+r.insurance_fee+r.partner_fee+r.commision-r.discount_fee) as total
        FROM (
        select p.code as partner_name,
        b.name as bank_name,
        pbd.account_number as bank_number,
        dh.receipt,
        dh.created_at,
        c.codeable_id,
        weight,
        COALESCE(pp.amount, 0) as pickup_fee,
        COALESCE(packing_fee, 0) as packing_fee,
        COALESCE(pp6.amount, 0) as packing_bike_fee,
        COALESCE(insurance_fee, 0) insurance_fee,
        COALESCE(pp5.amount * 0.3, 0) as partner_fee,
        COALESCE(pp4.amount, 0) as discount_fee,
        CASE
            WHEN weight>90 THEN COALESCE(pp5.amount * 0.05, 0)
            ELSE 0
        END  as commision
        from disbursment_histories dh
        left join partner_balance_disbursement pbd on dh.disbursment_id = pbd.id
        left join partners p on pbd.partner_id = p.id
        left join bank b on pbd.bank_id = b.id
        left join codes c on dh.receipt = c.content
        left join (	select pi2.package_id, sum(pi2.weight) as weight
                    from package_items pi2 where weight notnull group by 1) pi2
                    on pi2.package_id = c.codeable_id
        left join (	select pp.package_id, pp.amount
                    from package_prices pp where type = 'delivery' and description = 'pickup')pp
                    on pp.package_id = c.codeable_id
        left join (	select pp2.package_id, sum(pp2.amount) as packing_fee
                    from package_prices pp2 where type = 'handling' and description != 'bike' group by 1) pp2
                    on pp2.package_id = c.codeable_id
        left join (	select pp3.package_id, sum(pp3.amount) as insurance_fee
                    from package_prices pp3 where type = 'insurance' and description = 'insurance' group by 1) pp3
                    on pp3.package_id = c.codeable_id
        left join (	select pp4.package_id, pp4.amount
                    from package_prices pp4 where type = 'discount' and description = 'service') pp4
                    on pp4.package_id = c.codeable_id
        left join ( select pp5.package_id, pp5.amount from package_prices pp5 where type = 'service' and description = 'service') pp5
                    on pp5.package_id  = c.codeable_id
        left join ( select pp6.package_id, pp6.amount from package_prices pp6 where type = 'handling' and description = 'bike') pp6
                    on pp6.package_id = c.codeable_id
        ) r
        where 1=1
        and to_char(r.created_at,'YYYY-MM-DD') >= '%s'
        and to_char(r.created_at, 'YYYY-MM-DD') <= '%s'
        order by r.created_at asc";

        $q = sprintf($q, $param['start'], $param['end']);

        return $q;
    }

    /**
     * New Query For Get List Of Receipt
     * Within Partner.
     * */
    private function newQueryDetailDisbursment($partnerId)
    {
        $q = "select c.content as receipt, p.total_amount as total_payment, pbh.balance as total_accepted from partner_balance_disbursement pbd
        left join (
            select pbh.partner_id, pbh.package_id, sum(pbh.balance) as balance from partner_balance_histories pbh where pbh.package_id notnull group by pbh.package_id, pbh.partner_id
            ) pbh
            on pbd.partner_id = pbh.partner_id
        left join (
            select * from codes c where codeable_type = 'App\Models\Packages\Package'
            ) c
            on pbh.package_id = c.codeable_id
        left join packages p on pbh.package_id = p.id
        where pbd.partner_id = $partnerId";

        return $q;
    }

    /**
     * To get detail disbursment of partner transporter.
     * @param $partnerId
     */
    private function getDetailDisbursmentTransporter($partnerId)
    {
        $deliveryHistory = DeliveryHistory::with('deliveries.packages')->where('partner_id', $partnerId)->get()
            ->map(function ($q) {
                $amount = $q->deliveries->packages->sum('total_amount');
                $res = [
                    'receipt' => $q->deliveries->code->content,
                    'total_accepted' => $q->balance,
                    'total_payment' => $amount
                ];

                return $res;
            });

        return $deliveryHistory;
    }

    /**
     * Set detail disbursment of partner transporter.
     */
    private function detailDisbursTransporter($partnerType, $disbursment): JsonResponse
    {
        if ($partnerType == Partner::TYPE_TRANSPORTER) {
            $deliveries = $this->getDetailDisbursmentTransporter($disbursment->partner_id);

            $disbursHistory = DisbursmentHistory::all();

            if ($disbursment->status == Withdrawal::STATUS_REQUESTED) {
                $receiptRequested = $deliveries->whereNotIn('receipt', $disbursHistory->map(function ($r) {
                    return $r->receipt;
                })->values());

                $getPendingReceipts = $receiptRequested->map(function ($r) {
                    $r['approved'] = 'pending';
                    $r['total_payment'] = intval($r['total_payment']);
                    $r['total_accepted'] = intval($r['total_accepted']);
                    $r['approved_at'] = null;
                    return $r;
                })->values();

                $totalUnApproved = $getPendingReceipts->where('approved', 'pending')->map(function ($r) {
                    return $r;
                })->sum('total_accepted');

                $totalApproved = $getPendingReceipts->where('approved', 'success')->map(function ($r) {
                    return $r;
                })->sum('total_accepted');

                $approvedAt = $getPendingReceipts->whereNotNull('approved_at')->first();

                $attachment = $disbursment->attachment_transfer ?
                    Storage::disk('s3')->temporaryUrl('attachment_transfer/' . $disbursment->attachment_transfer, Carbon::now()->addMinutes(60)) :
                    null;

                $data = [
                    'transferred_at' => $disbursment->transferred_at,
                    'attachment_transfer' => $attachment,
                    'rows' => $getPendingReceipts,
                    'total_unapproved' => $totalUnApproved,
                    'total_approved' => $totalApproved,
                    'approved_at' => $approvedAt ? $approvedAt->approved_at : null
                ];

                return (new Response(Response::RC_SUCCESS, $data))->json();
            } else {
                $getDisburs = DisbursmentHistory::where('disbursment_id', $disbursment->id)->get();
                $alreadyDis = DisbursmentHistory::select('receipt')->where('disbursment_id', '!=', $disbursment->id)->whereIn('receipt', $deliveries->pluck('receipt'))->get();
                $receipts = $deliveries->filter(function ($r) use ($alreadyDis) {
                    $check = $alreadyDis->where('receipt', $r['receipt'])->first();
                    if ($check) {
                        return false;
                    }
                    return true;
                })->map(function ($r) use ($getDisburs, $disbursment) {
                    $r['approved'] = 'pending';
                    $r['total_payment'] = intval($r['total_payment']);
                    $r['total_accepted'] = intval($r['total_accepted']);
                    $r['approved_at'] = null;

                    $check = $getDisburs->where('receipt', $r['receipt'])->first();
                    if ($check) {
                        $date = $getDisburs->map(function ($time) {
                            return $time->created_at;
                        })->first();

                        $r['approved'] = 'success';
                        $r['approved_at'] = date('Y-m-d H:i:s', strtotime($date));
                    }
                    return $r;
                })->values();

                $totalUnApproved = $receipts->where('approved', 'pending')->map(function ($r) {
                    return $r;
                })->sum('total_accepted');

                $totalApproved = $receipts->where('approved', 'success')->map(function ($r) {
                    return $r;
                })->sum('total_accepted');

                $approvedAt = $receipts->whereNotNull('approved_at')->first();

                $attachment = $disbursment->attachment_transfer ?
                    Storage::disk('s3')->temporaryUrl('attachment_transfer/' . $disbursment->attachment_transfer, Carbon::now()->addMinutes(60)) :
                    null;

                $data = [
                    'transferred_at' => $disbursment->transferred_at,
                    'attachment_transfer' => $attachment,
                    'rows' => $receipts,
                    'total_unapproved' => $totalUnApproved,
                    'total_approved' => $totalApproved,
                    'approved_at' => $approvedAt ? $approvedAt['approved_at'] : null
                ];

                return (new Response(Response::RC_SUCCESS, $data))->json();
            }
        }
    }

    /**
     * Sub function for a get detail disburs.
     */
    private function detailDisburs($disbursment): JsonResponse
    {
        $query = $this->newQueryDetailDisbursment($disbursment->partner_id);
        $packages = collect(DB::select($query));

        $disbursHistory = DisbursmentHistory::with('parentDisbursment')->get()->filter(function ($q) use ($disbursment) {
            if ($q->parentDisbursment->partner_id !== $disbursment->partner_id) {
                return false;
            }
            return true;
        });

        if ($disbursment->status == Withdrawal::STATUS_REQUESTED) {
            $data = $this->detailWithRequest($disbursment, $packages, $disbursHistory);
            return (new Response(Response::RC_SUCCESS, $data))->json();
        } else {
            $data = $this->detailWithApprove($disbursment, $packages);
            return (new Response(Response::RC_SUCCESS, $data))->json();
        }
    }

    /**
     * Approve packages by receipt code.
     */
    private function approveForPackages($disbursment, $receipt): JsonResponse
    {
        $query = $this->newQueryDetailDisbursment($disbursment->partner_id);
        $packages = collect(DB::select($query));

        $getReceipt = $packages->whereIn('receipt', $receipt)->map(function ($r) {
            $r->total_accepted = ceil($r->total_accepted);
            return $r;
        })->values();

        if ($getReceipt->isNotEmpty()) {
            $getReceipt->each(function ($r) use ($disbursment) {
                $disbursHistory = new DisbursmentHistory();
                $disbursHistory->disbursment_id = $disbursment->id;
                $disbursHistory->receipt = $r->receipt;
                $disbursHistory->amount = $r->total_accepted;
                $disbursHistory->status = DisbursmentHistory::STATUS_APPROVE;
                $disbursHistory->save();
            });

            $total_accepted = $getReceipt->sum('total_accepted');
            $calculate = $disbursment->first_balance - $total_accepted;

            if ($disbursment->first_balance !== $calculate) {
                $disbursment->amount = $total_accepted;
                $disbursment->status = Withdrawal::STATUS_APPROVED;
                $disbursment->action_by = Auth::id();
                $disbursment->action_at = Carbon::now();
                if ($disbursment->bank_id == 3) {
                    $disbursment->charge_admin = false;
                    $disbursment->fee_charge_admin = 0;
                } else {
                    $disbursment->charge_admin = true;
                    $disbursment->fee_charge_admin = 6500;
                    $disbursment->amount = $disbursment->amount - $disbursment->fee_charge_admin;
                }
                $disbursment->save();

                $partners = Partner::where('id', $disbursment->partner_id)->first();
                $partners->balance = $calculate;
                $partners->save();
            } else {
                return (new Response(Response::RC_BAD_REQUEST))->json();
            }

            return (new Response(Response::RC_UPDATED, $disbursment))->json();
        } else {
            return (new Response(Response::RC_BAD_REQUEST))->json();
        }
    }

    /**
     * Approve deliveries by with manifest code.
     */
    private function approveForDeliveries($disbursment, $receipt): JsonResponse
    {
        $deliveries = $this->getDetailDisbursmentTransporter($disbursment->partner_id);
        $getReceipt = $deliveries->whereIn('receipt', $receipt)->map(function ($r) {
            $r['total_accepted'] = ceil($r['total_accepted']);
            return $r;
        })->values();

        if ($getReceipt->isNotEmpty()) {
            $getReceipt->each(function ($r) use ($disbursment) {
                $disbursHistory = new DisbursmentHistory();
                $disbursHistory->disbursment_id = $disbursment->id;
                $disbursHistory->receipt = $r['receipt'];
                $disbursHistory->amount = $r['total_accepted'];
                $disbursHistory->status = DisbursmentHistory::STATUS_APPROVE;
                $disbursHistory->save();
            });

            $total_accepted = $getReceipt->sum('total_accepted');
            $calculate = $disbursment->first_balance - $total_accepted;

            if ($disbursment->first_balance !== $calculate) {
                $disbursment->amount = $total_accepted;
                $disbursment->status = Withdrawal::STATUS_APPROVED;
                $disbursment->action_by = Auth::id();
                $disbursment->action_at = Carbon::now();
                if ($disbursment->bank_id == 3) {
                    $disbursment->charge_admin = false;
                    $disbursment->fee_charge_admin = 0;
                } else {
                    $disbursment->charge_admin = true;
                    $disbursment->fee_charge_admin = 6500;
                    $disbursment->amount = $disbursment->amount - $disbursment->fee_charge_admin;
                }
                $disbursment->save();

                $partners = Partner::where('id', $disbursment->partner_id)->first();
                $partners->balance = $calculate;
                $partners->save();
            } else {
                return (new Response(Response::RC_BAD_REQUEST))->json();
            }

            return (new Response(Response::RC_UPDATED, $disbursment))->json();
        } else {
            return (new Response(Response::RC_BAD_REQUEST))->json();
        }
    }

    private function detailWithApprove($disbursment, $packages): array
    {
        $getDisburs = DisbursmentHistory::where('disbursment_id', $disbursment->id)->get();

        $alreadyDis = DisbursmentHistory::select('receipt')->where('disbursment_id', '!=', $disbursment->id)->whereIn('receipt', $packages->pluck('receipt'))->get();

        $receipts = $packages->map(function ($r) use ($getDisburs) {
            $r->approved = 'pending';
            $r->total_payment = intval($r->total_payment);
            $r->total_accepted = intval($r->total_accepted);
            $r->approved_at = null;

            $check = $getDisburs->where('receipt', $r->receipt)->first();
            if ($check) {
                $date = $getDisburs->map(function ($time) {
                    return $time->created_at;
                })->first();

                $r->approved = 'success';
                $r->approved_at = date('Y-m-d H:i:s', strtotime($date));
            }
            return $r;
        })->values();

        $totalUnApproved = $receipts->where('approved', 'pending')->map(function ($r) {
            return $r;
        })->sum('total_accepted');

        $totalApproved = $receipts->where('approved', 'success')->map(function ($r) {
            return $r;
        })->sum('total_accepted');

        $approvedAt = $receipts->whereNotNull('approved_at')->first();

        $attachment = $disbursment->attachment_transfer ?
            Storage::disk('s3')->temporaryUrl('attachment_transfer/' . $disbursment->attachment_transfer, Carbon::now()->addMinutes(60)) :
            null;

        $filteredReceipts = $receipts->filter(function ($r) use ($alreadyDis) {
            $check = $alreadyDis->where('receipt', $r->receipt)->first();
            if ($check) {
                return false;
            }
            return true;
        })->values();

        $data = [
            'transferred_at' => $disbursment->transferred_at,
            'attachment_transfer' => $attachment,
            // 'rows' => $receipts,
            'rows' => $filteredReceipts,
            'total_unapproved' => $totalUnApproved,
            'total_approved' => $totalApproved,
            'approved_at' => $approvedAt ? $approvedAt->approved_at : null,
            'partner_code' => $disbursment->partner->code
        ];

        return $data;
    }

    private function detailWithRequest($disbursment, $packages, $disbursHistory): array
    {
        $receiptRequested = $packages->whereNotIn('receipt', $disbursHistory->map(function ($r) {
            return $r->receipt;
        })->values());

        $getPendingReceipts = $receiptRequested->map(function ($r) {
            $r->approved = 'pending';
            $r->total_payment = intval($r->total_payment);
            $r->total_accepted = intval($r->total_accepted);
            $r->approved_at = null;
            return $r;
        })->values();

        $totalUnApproved = $getPendingReceipts->where('approved', 'pending')->map(function ($r) {
            return $r;
        })->sum('total_accepted');

        $totalApproved = $getPendingReceipts->where('approved', 'success')->map(function ($r) {
            return $r;
        })->sum('total_accepted');

        $approvedAt = $getPendingReceipts->whereNotNull('approved_at')->first();

        $attachment = $disbursment->attachment_transfer ?
            Storage::disk('s3')->temporaryUrl('attachment_transfer/' . $disbursment->attachment_transfer, Carbon::now()->addMinutes(60)) :
            null;

        $data = [
            'transferred_at' => $disbursment->transferred_at,
            'attachment_transfer' => $attachment,
            'rows' => $getPendingReceipts,
            'total_unapproved' => $totalUnApproved,
            'total_approved' => $totalApproved,
            'approved_at' => $approvedAt ? $approvedAt->approved_at : null,
            'partner_code' => $disbursment->partner->code
        ];

        return $data;
    }
}
