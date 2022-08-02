<?php

namespace App\Http\Controllers\Api\Partner\Owner;

use App\Concerns\Controllers\HasResource;
use App\Events\Partners\Balance\WithdrawalRequested;
use App\Http\Controllers\Api\Internal\FinanceController;
use App\Http\Controllers\Controller;
use App\Http\Resources\Account\UserBankResource;
use App\Http\Resources\Api\Partner\Owner\WithdrawalResource;
use App\Http\Response;
use App\Jobs\Partners\CreateNewBalanceDisbursement;
use App\Models\Partners\Balance\DisbursmentHistory;
use App\Models\Partners\BankAccount;
use App\Models\Payments\Bank;
use App\Models\Payments\Withdrawal;
use App\Supports\Repositories\PartnerRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        if ($repository->getPartner()->balance < $request->amount) {
            return (new Response(Response::RC_INSUFFICIENT_BALANCE))->json();
        }

        $request['status'] = Withdrawal::STATUS_REQUESTED;
        $job = new CreateNewBalanceDisbursement($repository->getPartner(), $request->all());
        $this->dispatch($job);

        event(new WithdrawalRequested($job->withdrawal));

        return $this->jsonSuccess(new WithdrawalResource($job->withdrawal));
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
            return (new Response(Response::RC_SUCCESS, $result))->json();
        } else if ($withdrawal->status == Withdrawal::STATUS_PENDING) {
            $pendingResult = DisbursmentHistory::where('disbursment_id', $withdrawal->id)->where('status', DisbursmentHistory::STATUS_WAITING_FOR_APPROVE)->paginate(10);
            return (new Response(Response::RC_SUCCESS, $pendingResult))->json();
        } else {
            /** Todo Show Request Receipts for withdrawal */

            // $receipts = $this->getReceivedReceipts($withdrawal);
            // $getReceipts = collect(DB::select($receipts));
            
            // $disbursment = DisbursmentHistory::all();
            // dump($disbursment);
            // foreach ($disbursment as $key) {
            //     $getReceipts->where('receipt', '!=', $key->receipt)->map(function ($r) {
            //         return $r;
            //     })->values();
            // }
            // dump($getReceipts);
            // return (new Response(Response::RC_SUCCESS, $getReceipts))->json();
        }
        /** End todo */
    }

    private function getExistingReceipt()
    {
        $query = "SELECT * FROM disbursment_histories";
        return $query;
    }

    private function getReceivedReceipts($request)
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
}
