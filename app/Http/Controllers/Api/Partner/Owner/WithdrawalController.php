<?php

namespace App\Http\Controllers\Api\Partner\Owner;

use App\Concerns\Controllers\HasResource;
use App\Events\Partners\Balance\WithdrawalRequested;
use App\Exports\WithdrawalExport;
use App\Http\Controllers\Api\Internal\FinanceController;
use App\Http\Controllers\Controller;
use App\Http\Resources\Account\UserBankResource;
use App\Http\Resources\Api\Partner\Owner\WithdrawalResource;
use App\Http\Response;
use App\Jobs\Partners\CreateNewBalanceDisbursement;
use App\Models\Code;
use App\Models\Packages\Package;
use App\Models\Partners\Balance\DisbursmentHistory;
use App\Models\Partners\BankAccount;
use App\Models\Payments\Bank;
use App\Models\Payments\Withdrawal;
use App\Supports\Repositories\PartnerRepository;
use Carbon\Carbon;
use Google\Cloud\Storage\Connection\Rest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Str;
use function PHPUnit\Framework\isNull;

class WithdrawalController extends Controller
{
    use HasResource, DispatchesJobs;
    /**
     * @var array
     */
    protected array $attributes;

    /**
     * @var Builder
     */
    protected Builder $query;

    /**
     * @var string
     */
    protected string $model = Withdrawal::class;

    /**
     * @var array
     */
    protected array $rules;

    public function __construct()
    {
        $this->rules = [
            'q' => ['nullable'],
        ];

        $this->baseBuilder();
    }

    public function getSearch(Request $request)
    {
        $this->query->whereHas('partner', function ($query) use ($request) {
            $query->search($request->q, 'code');
        });
        return $this;
    }

    public function index(Request $request): JsonResponse
    {
        $account = $request->user();
        $this->query->where('partner_id', $account->partners[0]->id);

        $this->query->when($request->input('status'), fn (Builder $builder, $input) => $builder->where('status', $input));
        if ($request->to == null) {
            $request->to = Carbon::now();
        }
        $this->query->when(request()->has('from'), fn ($q) => $q->whereBetween('created_at', [$request->from, $request->to]));

        if ($request->q != null) {
            $this->getSearch($request);
        }

        return (new Response(Response::RC_SUCCESS, $this->query->paginate(request('per_page', 15))))->json();
    }

    public function store(Request $request, PartnerRepository $repository): JsonResponse
    {
        $withdrawal = Withdrawal::where('partner_id', $repository->getPartner()->id)->where('status', Withdrawal::STATUS_REQUESTED)
            ->orWhere('status', Withdrawal::STATUS_APPROVED)->orderBy('created_at', 'desc')->first();
        $currentDate = Carbon::now();

        if (is_null($withdrawal)) {

            $currentTime = Carbon::now();
            $expiredTime = $currentTime->addDays(7);
            $request['expired_at'] = $expiredTime;
            $request['status'] = Withdrawal::STATUS_REQUESTED;

            $job = new CreateNewBalanceDisbursement($repository->getPartner(), $request->all());
            $this->dispatch($job);

            event(new WithdrawalRequested($job->withdrawal));

            return $this->jsonSuccess(new WithdrawalResource($job->withdrawal));
        } else if (!empty($withdrawal)) {
            if ($currentDate < $withdrawal->expired_at) {
                return (new Response(Response::RC_BAD_REQUEST))->json();
            }

            $currentTime = Carbon::now();
            $expiredTime = $currentTime->addDays(7);

            $request['expired_at'] = $expiredTime;
            $request['status'] = Withdrawal::STATUS_REQUESTED;

            $job = new CreateNewBalanceDisbursement($repository->getPartner(), $request->all());
            $this->dispatch($job);

            event(new WithdrawalRequested($job->withdrawal));

            return $this->jsonSuccess(new WithdrawalResource($job->withdrawal));
        }
    }

    public function attachmentTransfer(Request $request,Withdrawal $wd): JsonResponse
    {
        $request->validate([
            'attachment_transfer' => ['required','image','mimes:png,jpg,jpeg']
        ]);
        $withdrawal = Withdrawal::where('id',$request->id)->first();
        if($withdrawal->status == Withdrawal::STATUS_APPROVED) {
            $attachment = $request->attachment_transfer;
            $attachment_extension = $attachment->getClientOriginalExtension();
            $fileName = bin2hex(random_bytes(20)).'.'.$attachment_extension;
            $attachment->move('attachment_transfer',$fileName);

            // Update table partner_balance_disbursement and attach the image
            $withdrawal->attachment_transfer = $fileName;
            $withdrawal->status = Withdrawal::STATUS_TRANSFERRED;
            $withdrawal->save();

            $data = [
                'attachment' => asset('attachment_transfer/'.$withdrawal->attachment_transfer),
                // 'attachment_transfer' => $fileName,
            ];
            return (new Response(Response::RC_CREATED,$data))->json();
        } else {
            return (new Response(Response::RC_INVALID_DATA,[]))->json();
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getAccountBank(Request $request): JsonResponse
    {
        $account = $request->user();
        $query = BankAccount::query();
        $query->where('user_id', $account->id);

        return $this->jsonSuccess(UserBankResource::collection($query->paginate(request('per_page', 15))));
    }

    /**
     * @return JsonResponse
     */
    public function getBank(): JsonResponse
    {
        $bank  = Bank::where('is_active', true)->get();

        return (new Response(Response::RC_SUCCESS, $bank))->json();
    }

    /**
     * @return JsonResponse
     * Todo Status Of Withdrawal Partners
     */
    public function detail(Withdrawal $withdrawal)
    {
        if ($withdrawal->status == Withdrawal::STATUS_APPROVED) {
            $result = DisbursmentHistory::where('disbursment_id', $withdrawal->id)->where('status', DisbursmentHistory::STATUS_APPROVE)->paginate(10);
            $data = [
                'attachment_transfer' => asset('attachment_transfer/'.$withdrawal->attachment_transfer),
                'result' => $result
            ];
            return (new Response(Response::RC_SUCCESS, $data))->json();
        } else {
            $pendingReceipts = $this->getPendingReceipt($withdrawal);
            $toCollect = collect(DB::select($pendingReceipts));

            $toCollect->map(function ($r) use ($withdrawal) {
                $r->created_at = $withdrawal->created_at;
                return $r;
            })->values();

            $data = $this->paginate($toCollect);
            return (new Response(Response::RC_SUCCESS, $data))->json();
        }
        /** End todo */
    }

    public function detailReceipt(Withdrawal $withdrawal, $receipt)
    {
        $this->withdrawal = $withdrawal;

        $code = Code::where('content', $receipt)->where('codeable_type', Package::class)->first();
        if (is_null($code)) {
            return (new Response(Response::RC_DATA_NOT_FOUND));
        }

        $data = $code->codeable->prices;
        $receipt = [
            'receipt' => $receipt,
            'total_amount' => $data->sum('amount')
        ];

        return (new Response(Response::RC_SUCCESS, [$data, $receipt]))->json();
    }

    public function export(Withdrawal $withdrawal)
    {
        $result = DisbursmentHistory::query()->select('disbursment_histories.receipt as receipt', 'disbursment_histories.amount as amount')
            ->leftJoin('partner_balance_disbursement as pbd', 'disbursment_histories.disbursment_id', '=', 'pbd.id')
            ->where('pbd.partner_id', $withdrawal->partner_id)->where('disbursment_histories.disbursment_id', $withdrawal->id)
            ->get()->map(function ($row, $index) {
            $row->no = $index + 1;
            return $row;
        });

        return (new WithdrawalExport($result))->download('Withdrawal-Histories.xlsx');
    }

    private function getPendingReceipt($request)
    {
        $query = "SELECT
            r.total_payment,
            r.receipt,
            (r.pickup_fee + r.packing_fee + r.insurance_fee + r.partner_fee + r.extra_charge - r.discount) as amount
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

        return $query;
    }
}
