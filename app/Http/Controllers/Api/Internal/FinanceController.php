<?php

namespace App\Http\Controllers\Api\Internal;

use App\Exports\DisbursmentExport;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Internal\Finance\ListResource;
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

    /**List disbursment */
    public function list(): JsonResponse
    {
        $result = Withdrawal::orderBy('created_at', 'desc')->paginate(10);
        return $this->jsonSuccess(ListResource::collection($result));
    }

    /**Detail disbursment */
    public function detail(Withdrawal $withdrawal, Request $request): JsonResponse
    {
        $result = Withdrawal::where('id', $request->id)->first();
        if (is_null($result)) {
            return (new Response(Response::RC_SUCCESS, []))->json();
        }

        $query = $this->detailDisbursment($result);
        $packages = collect(DB::select($query));

        $approveds = $this->getApprovedReceipt($result);
        $approves = collect(DB::select($approveds));

        $disbursHistory = DisbursmentHistory::all();

        if ($result->status == Withdrawal::STATUS_REQUESTED) {
            $receiptRequested = $packages->whereNotIn('receipt', $disbursHistory->map(function ($r) {
                return $r->receipt;
            })->values());

            $getPendingReceipts = $receiptRequested->map(function ($r) {
                $r->approved = 'pending';
                $r->total_payment = intval($r->total_payment);
                $r->commission_discount = intval($r->commission_discount);
                $r->approved_at = null;
                return $r;
            })->values();

            $approvedAt = $getPendingReceipts->whereNotNull('approved_at')->first();

            $data = [
                'rows' => $getPendingReceipts,
                'approved_at' => $approvedAt ? $approvedAt->approved_at : null
            ];

            return (new Response(Response::RC_SUCCESS, $data))->json();
        } else {
            // $receiptApproved = DisbursmentHistory::where('disbursment_id', $result->id)->get();

            // $receipts = $packages->whereNotIn('receipt', $receiptApproved->map(function ($r) {
            //     return $r->receipt;
            // })->values());
            // dd($receipts);

            $receipts = $packages->map(function ($r) use ($disbursHistory, $result) {
                $r->approved = 'pending';
                $r->total_payment = intval($r->total_payment);
                $r->commission_discount = intval($r->commission_discount);
                $r->approved_at = null;

                $check = $disbursHistory->where('receipt', $r->receipt)->first();
                if ($check) {
                    $r->approved = 'success';
                    $r->approved_at = date('Y-m-d H:i:s', strtotime($r->approved_at));
                }
                return $r;
            })->values();

            $approvedAt = $receipts->whereNotNull('approved_at')->first();

            $data = [
                'rows' => $receipts,
                'approved_at' => $approvedAt ? $approvedAt->approved_at : null
            ];

            return (new Response(Response::RC_SUCCESS, $data))->json();
        }
    }

    /**Submit Approved Disbursment */
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
                $disbursHistory->status = DisbursmentHistory::STATUS_APPROVE;
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

        if ($this->attributes['status'] == "requested") {
            $disbursmentStatus = Withdrawal::where('status', $this->attributes['status'])->orderByDesc('created_at')->paginate(10);
            return $this->jsonSuccess(ListResource::collection($disbursmentStatus));
        } else if ($this->attributes['status'] == "approved") {
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
            $r->commission_discount = intval($r->commission_discount);

            $disbursHistory = DisbursmentHistory::where('receipt', $this->attributes['receipt'])->first();
            if (is_null($disbursHistory)) {
                $r->approved = 'pending';
                return $r;
            } else if ($disbursHistory->receipt == $r->receipt) {
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
            $data = array($receipt);
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
        header("Content-type: application/vnd-ms-excel");
        header("Cache-Control: max-age=0");

        return view('report.finance', compact('result'));
    }

    /**Add report excel for disbursment */
    public function export(Request $request)
    {
        $request->validate([
            'start' => 'required|date_format:Y-m-d',
            'end' => 'required|date_format:Y-m-d',
        ]);

        $param = [
            'start' => $request->get('start', Carbon::now()->subMonth()->format('Y-m-d')),
            'end' => $request->get('end', Carbon::now()->format('Y-m-d')),
        ];

        $result = $this->getQueryExports($param);
        $data = collect(DB::select($result))->toArray();

        return (new DisbursmentExport($data))->download('Disbursment-Histories.xlsx');
    }

    private function reportReceiptQuery($param)
    {
        $q = "select
        c.content as receipt_code,
        gr2.name as origin_city,
        gp.name as destination_province,
        gr.name as destination_city,
        gd.name as destination_district,
        gsd.name as destination_sub_district,
        gsd.zip_code as zip_code,
        case
          when p.transporter_type is null then 'walk-in'
          else 'by apps'
        end as type_order,
        case
          when p.transporter_type is null then '-'
          else p.transporter_type
        end as transporter_pickup_type,
        d2.updated_at as unloaded_at,
        p2.code as origin_partner,
        p3.payment_ref_id as nicepay_trx_id,
        p3.status as nicepay_status,
        p3.confirmed_at as payment_verified_at,
        p3.created_at as payment_request_at,
        p.total_weight as total_weight,
        coalesce((select sum(pi3.price) from package_items pi3 where pi3.package_id = p.id and pi3.is_insured = true), 0) as item_price,
          coalesce((select pp.amount from package_prices pp where pp.package_id = p.id and pp.type='service'), 0) as total_delivery_price,
          coalesce((select pp2.amount from package_prices pp2 where pp2.package_id = p.id and pp2.type = 'discount' and pp2.description='service'), 0) as discount_delivery,
          coalesce((select calculate_extra_commission(total_weight, (select pp.amount from package_prices pp where pp.package_id = p.id and pp.type='service'))),0 ) as extra_commission,
          coalesce((select calculate_commission(p2.type, (select pp.amount from package_prices pp where pp.package_id = p.id and pp.type='service'))),0 ) as commission_manual,
        coalesce(((select public.calculate_commission(p2.type, (select pp.amount from package_prices pp where pp.package_id = p.id and pp.type='service'))) - (coalesce((select pp2.amount from package_prices pp2 where pp2.package_id = p.id and pp2.type = 'discount' and pp2.description='service'), 0)) + (coalesce((select public.calculate_extra_commission(total_weight, (select pp.amount from package_prices pp where pp.package_id = p.id and pp.type='service'))),0 ))), 0) as total_commission,
        coalesce((select calculate_package_price_by_package_id_and_type(p.id, 'handling')), 0) as receipt_total_packing_price,
        coalesce((select calculate_package_price_by_package_id_and_type(p.id, 'insurance')), 0) as receipt_insurance_price,
        coalesce((select sum(pp3.amount) from package_prices pp3 where pp3.package_id = p.id and pp3.type = 'delivery' and pp3.description = 'pickup'), 0) as receipt_pickup_price,
        p.total_amount as receipt_total_amount
      from
        deliverables d
      left join packages p on
        p.id = d.deliverable_id
        and d.deliverable_type = 'App\Models\Packages\Package'
      left join codes c on
        (p.id = c.codeable_id
          and c.codeable_type = 'App\Models\Packages\Package')
      left join deliveries d2 on
        d.delivery_id = d2.id
      left join partners p2 on
        p2.id = d2.partner_id
      inner join payments p3 on
        p.id = p3.payable_id
        and p3.payable_type = 'App\Models\Packages\Package'
        and p3.status = 'success'
      left join geo_sub_districts gsd on
        p.destination_sub_district_id = gsd.id 
      left join geo_districts gd on
          gsd.district_id = gd.id
      left join geo_regencies gr on
          gd.regency_id = gr.id
      left join geo_provinces gp on
          gr.province_id = gp.id
      left join geo_regencies gr2 on 
          p.origin_regency_id = gr2.id
      where
        d2.type = 'pickup'
        and d2.status = 'finished'
        and p.payment_status = 'paid'
        and DATE(p3.confirmed_at) >= '%s'
        and DATE(p3.confirmed_at) <= '%s'
      order by
        p3.confirmed_at
        LIMIT 20";

        $q = sprintf($q, $param['start'], $param['end']);

        return $q;
    }

    public static function detailDisbursment($request)
    {
        $q =
            "SELECT p.total_amount total_payment, c.content receipt, p.total_amount * 0.3 as commission_discount

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
    private function getApprovedDisbursment($receipts)
    {
        $q = "SELECT * FROM disbursment_histories WHERE receipt IN ('%s')";
        $q = sprintf($q, implode(',', $receipts));

        return $q;
    }
    /**End query */

    private function getApprovedReceipt($request)
    {
        $query =
            "SELECT p.total_amount as total_payment, dh.receipt as receipt, dh.amount as commission_discount, dh.created_at as approved_at 
        from disbursment_histories dh
        left join partner_balance_disbursement pbd on dh.disbursment_id = pbd.id
        left join codes c on dh.receipt = c.content
        left join packages p on c.codeable_id = p.id
        where dh.disbursment_id = $request->id";

        return $query;
    }

    private function getLatestApprovedReceipts($request)
    {
        $query =
            "SELECT * FROM disbursment_histories dh2 
        WHERE disbursment_id = (
            SELECT max(disbursment_id) FROM disbursment_histories dh
            LEFT JOIN partner_balance_disbursement pbd ON dh.disbursment_id = pbd.id 
            WHERE pbd.partner_id = $request->partner_id
        LIMIT 1
        )";

        return $query;
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
        r.insurance_fee,
        r.partner_fee,
        r.discount_fee,
        r.commision,
        (r.pickup_fee+r.packing_fee+r.insurance_fee+r.partner_fee+r.commision-r.discount_fee) as total
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
                from package_prices pp2 where type = 'handling' group by 1) pp2 
                on pp2.package_id = c.codeable_id
    left join (	select pp3.package_id, sum(pp3.amount) as insurance_fee 
                from package_prices pp3 where type = 'insurance' and description = 'insurance' group by 1) pp3
                on pp3.package_id = c.codeable_id
    left join (	select pp4.package_id, pp4.amount 
                from package_prices pp4 where type = 'discount') pp4 
                on pp4.package_id = c.codeable_id
    left join ( select pp5.package_id, pp5.amount from package_prices pp5 where type = 'service' and description = 'service') pp5
                on pp5.package_id  = c.codeable_id
    ) r
    and date(r.created_at) >= '%s'
    and date(r.created_at) <= '%s'
    order by r.created_at ASC";

        $q = sprintf($q, $param['start'], $param['end']);

        return $q;
    }
}
